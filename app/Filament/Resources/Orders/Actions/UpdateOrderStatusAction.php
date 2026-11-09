<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Actions;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderStatusService;
use App\Services\SteadfastCourier;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Illuminate\Http\Client\ConnectionException;
use InvalidArgumentException;
use RuntimeException;

/**
 * Moves an order to one of the statuses its current status allows. Cancelling and
 * refunding need their own permissions on top of order update access. Confirming
 * also books the order with Steadfast when auto-send is on.
 */
class UpdateOrderStatusAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'updateStatus';
    }

    /**
     * Next statuses the signed-in staff member may move this order to.
     *
     * @return list<OrderStatus>
     */
    public static function allowedStatuses(Order $order): array
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return [];
        }

        return array_values(array_filter(
            $order->status->transitions(),
            fn (OrderStatus $status): bool => match ($status) {
                OrderStatus::Cancelled => $user->can('cancel', $order),
                OrderStatus::Refunded => $user->can('refund', $order),
                default => $user->can('update', $order),
            },
        ));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Update status')
            ->icon('heroicon-o-arrow-path-rounded-square')
            ->modalIcon('heroicon-o-arrow-path-rounded-square')
            ->modalHeading(fn (Order $record): string => "Update order {$record->order_number}")
            ->modalDescription(fn (Order $record): string => "Current status: {$record->status->getLabel()}")
            ->modalSubmitActionLabel('Save status')
            ->modalWidth('lg')
            ->visible(fn (Order $record): bool => self::allowedStatuses($record) !== [])
            ->schema(fn (Order $record): array => [
                ToggleButtons::make('status')
                    ->label('Move to')
                    ->options(collect(self::allowedStatuses($record))->mapWithKeys(fn (OrderStatus $status): array => [$status->value => $status->getLabel()])->all())
                    ->colors(collect(self::allowedStatuses($record))->mapWithKeys(fn (OrderStatus $status): array => [$status->value => $status->getColor()])->all())
                    ->inline()
                    ->required(),
                Textarea::make('note')
                    ->label('Note')
                    ->placeholder('Optional, shown in the order timeline')
                    ->rows(3)
                    ->maxLength(500),
            ])
            ->action(function (Order $record, array $data): void {
                $status = OrderStatus::from($data['status']);

                try {
                    if (! in_array($status, self::allowedStatuses($record), true)) {
                        throw new InvalidArgumentException('You are not allowed to make this change.');
                    }

                    app(OrderStatusService::class)->transition($record, $status, $data['note'] ?? null, auth()->user());
                } catch (InvalidArgumentException $exception) {
                    Notification::make()
                        ->title('Status not changed')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                $record->load('statusHistories.user');

                if ($status === OrderStatus::Confirmed) {
                    self::sendConfirmedOrderToSteadfast($record);

                    return;
                }

                Notification::make()
                    ->title("Order marked as {$status->getLabel()}")
                    ->success()
                    ->send();
            });
    }

    /**
     * A confirmed order goes to Steadfast straight away when auto-send is on. If Steadfast
     * refuses it the order stays confirmed and staff see why, so they can fix and resend it.
     */
    private static function sendConfirmedOrderToSteadfast(Order $order): void
    {
        try {
            $trackingCode = app(SteadfastCourier::class)->sendConfirmedOrder($order);
        } catch (ConnectionException|RuntimeException $exception) {
            Notification::make()
                ->title('Order confirmed, but not sent to Steadfast')
                ->body($exception->getMessage().' Fix it and use "Send to Steadfast" to try again.')
                ->warning()
                ->persistent()
                ->send();

            return;
        }

        if ($trackingCode === null) {
            Notification::make()->title('Order marked as Confirmed')->success()->send();

            return;
        }

        Notification::make()
            ->title('Order confirmed and sent to Steadfast')
            ->body("Tracking code: {$trackingCode}")
            ->success()
            ->send();
    }
}
