<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Actions;

use App\Enums\OrderStatus;
use App\Filament\Pages\ManageSteadfastCourier;
use App\Models\Order;
use App\Services\OrderStatusService;
use App\Services\SteadfastCourier;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Http\Client\ConnectionException;
use InvalidArgumentException;
use RuntimeException;

/**
 * Books the order with Steadfast Courier and saves the tracking code on it. Shown while
 * the order has not been handed to a courier yet; if Steadfast is not set up, clicking it
 * points to the Steadfast settings instead. A pending order is marked Confirmed first,
 * since only confirmed orders go to the courier.
 */
class SendToSteadfastAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'sendToSteadfast';
    }

    public static function canSend(Order $order): bool
    {
        return blank($order->tracking_number)
            && in_array($order->status, [OrderStatus::Pending, OrderStatus::Confirmed, OrderStatus::Processing, OrderStatus::Packed], true);
    }

    /**
     * Confirms a pending order, then books it with Steadfast. If Steadfast refuses, the
     * order stays confirmed so it can be fixed and sent again.
     *
     * @throws ConnectionException when Steadfast cannot be reached
     * @throws RuntimeException when Steadfast refuses the order
     * @throws InvalidArgumentException when the order can no longer be confirmed
     */
    public static function send(Order $order, SteadfastCourier $steadfast): string
    {
        if ($order->status === OrderStatus::Pending) {
            app(OrderStatusService::class)->transition($order, OrderStatus::Confirmed, 'Confirmed when sent to Steadfast', auth()->user());
        }

        return $steadfast->createConsignment($order);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (Order $record): string => $record->status === OrderStatus::Pending ? 'Confirm & send to Steadfast' : 'Send to Steadfast')
            ->icon('heroicon-o-paper-airplane')
            ->color('success')
            ->authorize('update')
            ->visible(fn (Order $record): bool => self::canSend($record))
            ->modal(fn (SteadfastCourier $steadfast): bool => $steadfast->enabled())
            ->requiresConfirmation()
            ->modalIcon('heroicon-o-truck')
            ->modalHeading(fn (Order $record): string => $record->status === OrderStatus::Pending
                ? "Confirm order {$record->order_number} and send it to Steadfast?"
                : "Send order {$record->order_number} to Steadfast?")
            ->modalDescription(fn (Order $record): string => sprintf(
                '%sSteadfast will collect %s on delivery from %s (%s).',
                $record->status === OrderStatus::Pending ? 'The order will be marked as Confirmed. ' : '',
                Money::format(SteadfastCourier::codAmount($record)),
                $record->customer_name,
                $record->customer_phone,
            ))
            ->modalSubmitActionLabel(fn (Order $record): string => $record->status === OrderStatus::Pending ? 'Confirm & send' : 'Send')
            ->action(function (Order $record, SteadfastCourier $steadfast): void {
                if (! $steadfast->enabled()) {
                    Notification::make()
                        ->title('Steadfast is not set up')
                        ->body('Turn Steadfast on and save the API key and secret key, then send the order again.')
                        ->warning()
                        ->actions([
                            Action::make('openSteadfastSettings')
                                ->label('Open Steadfast settings')
                                ->button()
                                ->url(ManageSteadfastCourier::getUrl()),
                        ])
                        ->send();

                    return;
                }

                $wasPending = $record->status === OrderStatus::Pending;

                try {
                    $trackingCode = self::send($record, $steadfast);
                } catch (ConnectionException|RuntimeException|InvalidArgumentException $exception) {
                    Notification::make()
                        ->title($wasPending && $record->status === OrderStatus::Confirmed ? 'Order confirmed, but not sent to Steadfast' : 'Order not sent to Steadfast')
                        ->body($exception->getMessage())
                        ->danger()
                        ->persistent()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title($wasPending ? 'Order confirmed and sent to Steadfast' : 'Order sent to Steadfast')
                    ->body("Tracking code: {$trackingCode}")
                    ->success()
                    ->send();
            });
    }
}
