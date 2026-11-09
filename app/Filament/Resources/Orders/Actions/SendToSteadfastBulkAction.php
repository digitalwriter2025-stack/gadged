<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Actions;

use App\Models\Order;
use App\Services\SteadfastCourier;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;

/**
 * Books the selected orders with Steadfast one by one, confirming pending ones first. Orders
 * already with a courier or past packing are skipped; any Steadfast refuses are listed with the reason.
 */
class SendToSteadfastBulkAction extends BulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'sendToSteadfastBulk';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Send to Steadfast')
            ->icon('heroicon-o-paper-airplane')
            ->color('success')
            ->authorizeIndividualRecords('update')
            ->visible(fn (SteadfastCourier $steadfast): bool => $steadfast->enabled())
            ->requiresConfirmation()
            ->modalIcon('heroicon-o-truck')
            ->modalHeading('Send selected orders to Steadfast?')
            ->modalDescription('Each order is booked as a separate parcel. Pending orders are marked Confirmed; orders already sent to a courier are skipped.')
            ->modalSubmitActionLabel('Send')
            ->deselectRecordsAfterCompletion()
            ->action(fn (Collection $records, SteadfastCourier $steadfast) => self::send($records, $steadfast));
    }

    /**
     * @param  Collection<int, Order>  $records
     */
    private static function send(Collection $records, SteadfastCourier $steadfast): void
    {
        $sent = 0;
        $failures = [];

        foreach ($records->filter(fn (Order $order): bool => SendToSteadfastAction::canSend($order)) as $order) {
            try {
                SendToSteadfastAction::send($order, $steadfast);
                $sent++;
            } catch (ConnectionException|RuntimeException|InvalidArgumentException $exception) {
                $failures[] = "{$order->order_number}: {$exception->getMessage()}";
            }
        }

        if ($sent === 0 && $failures === []) {
            Notification::make()
                ->title('Nothing to send')
                ->body('The selected orders are already with a courier or have shipped.')
                ->warning()
                ->send();

            return;
        }

        $title = $sent.' '.str('order')->plural($sent).' sent to Steadfast';

        if ($failures === []) {
            Notification::make()->title($title)->success()->send();

            return;
        }

        Notification::make()
            ->title($title.', '.count($failures).' not sent')
            ->body(collect($failures)->map(fn (string $failure): string => e($failure))->implode('<br>'))
            ->status($sent === 0 ? 'danger' : 'warning')
            ->persistent()
            ->send();
    }
}
