<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\Actions\SendToSteadfastAction;
use App\Filament\Resources\Orders\Actions\UpdateOrderStatusAction;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * @property Order $record
 */
class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return "Order {$this->record->order_number}";
    }

    public function getSubheading(): string
    {
        return 'Placed '.$this->record->created_at->timezone(config('app.display_timezone'))->format('d M Y \a\t h:i A')
            .' · '.$this->record->source->getLabel();
    }

    protected function resolveRecord(int|string $key): Model
    {
        return parent::resolveRecord($key)->load(['items', 'customer', 'statusHistories.user']);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('callCustomer')
                ->label('Call customer')
                ->icon('heroicon-o-phone')
                ->color('gray')
                ->url(fn (Order $record): string => 'tel:'.$record->customer_phone),

            Action::make('tracking')
                ->label('Courier & tracking')
                ->icon('heroicon-o-truck')
                ->color('gray')
                ->authorize('update')
                ->fillForm(fn (Order $record): array => [
                    'courier_name' => $record->courier_name,
                    'tracking_number' => $record->tracking_number,
                ])
                ->schema([
                    TextInput::make('courier_name')
                        ->label('Courier')
                        ->placeholder('e.g. Steadfast, Pathao, RedX')
                        ->maxLength(100),
                    TextInput::make('tracking_number')
                        ->label('Tracking / consignment number')
                        ->maxLength(100),
                ])
                ->modalWidth('md')
                ->action(function (Order $record, array $data): void {
                    $record->update($data);

                    Notification::make()->title('Courier details saved')->success()->send();
                }),

            SendToSteadfastAction::make(),

            UpdateOrderStatusAction::make(),
        ];
    }
}
