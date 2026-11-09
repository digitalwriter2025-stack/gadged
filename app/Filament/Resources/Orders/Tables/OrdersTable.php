<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderSource;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\Actions\SendToSteadfastAction;
use App\Filament\Resources\Orders\Actions\SendToSteadfastBulkAction;
use App\Filament\Resources\Orders\Actions\UpdateOrderStatusAction;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Order;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('items')->with('items:id,order_id,image_path'))
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->state(fn (Order $record): array => $record->items->pluck('image_path')->filter()->unique()->values()->all())
                    ->disk(config('media-library.disk_name'))
                    ->defaultImageUrl(ProductsTable::PLACEHOLDER_IMAGE)
                    ->square()
                    ->imageSize(44)
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText()
                    ->extraImgAttributes(['class' => 'gx-thumb', 'loading' => 'lazy']),

                TextColumn::make('order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable(['created_at'])
                    ->copyable()
                    ->copyMessage('Order number copied')
                    ->weight('bold')
                    ->description(fn (Order $record): string => $record->created_at->timezone(config('app.display_timezone'))->format('d M Y, h:i A')),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable(['customer_name', 'customer_phone', 'customer_email', 'shipping_district'])
                    ->weight('medium')
                    ->description(fn (Order $record): string => collect([$record->customer_phone, $record->shipping_district])->filter()->implode(' · ')),

                TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('BDT')
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->description(fn (Order $record): string => $record->items_count.' '.str('item')->plural($record->items_count)),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->description(fn (Order $record): ?string => $record->payment_method?->getLabel()),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('courier_name')
                    ->label('Courier')
                    ->state(fn (Order $record): ?string => SendToSteadfastAction::canSend($record) ? 'Send to courier' : $record->courier_name)
                    ->badge()
                    ->color('success')
                    ->icon(fn (Order $record): string => SendToSteadfastAction::canSend($record) ? 'heroicon-o-paper-airplane' : 'heroicon-o-truck')
                    ->placeholder('Not sent')
                    ->description(fn (Order $record): ?string => SendToSteadfastAction::canSend($record) ? null : $record->tracking_number)
                    ->searchable(['tracking_number'])
                    ->copyable(fn (Order $record): bool => ! SendToSteadfastAction::canSend($record) && filled($record->tracking_number))
                    ->copyableState(fn (Order $record): ?string => $record->tracking_number)
                    ->copyMessage('Tracking code copied')
                    ->action(SendToSteadfastAction::make())
                    ->disabledClick(fn (Order $record): bool => ! SendToSteadfastAction::canSend($record))
                    ->extraAttributes(fn (Order $record): array => SendToSteadfastAction::canSend($record) ? ['class' => 'gx-courier-send'] : [])
                    ->toggleable(),

                TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Placed')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('payment_status')
                    ->label('Payment status')
                    ->options(PaymentStatus::class),
                SelectFilter::make('payment_method')
                    ->label('Payment method')
                    ->options(PaymentMethod::class),
                SelectFilter::make('source')
                    ->options(OrderSource::class),
                TernaryFilter::make('sent_to_courier')
                    ->label('Courier')
                    ->placeholder('All orders')
                    ->trueLabel('Sent to courier')
                    ->falseLabel('Not sent yet')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('tracking_number'),
                        false: fn (Builder $query): Builder => $query->whereNull('tracking_number'),
                    ),
                Filter::make('created_at')
                    ->label('Order date')
                    ->schema([
                        DatePicker::make('from')->label('Placed from'),
                        DatePicker::make('until')->label('Placed until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date)))
                    ->indicateUsing(fn (array $data): array => array_filter([
                        'from' => ($data['from'] ?? null) ? 'From '.$data['from'] : null,
                        'until' => ($data['until'] ?? null) ? 'Until '.$data['until'] : null,
                    ])),
            ])
            ->filtersFormColumns(2)
            ->recordActions([
                UpdateOrderStatusAction::make()->iconButton()->tooltip('Update status'),
                ViewAction::make()->iconButton()->tooltip('Open order'),
            ])
            ->toolbarActions([
                SendToSteadfastBulkAction::make(),
            ])
            ->checkIfRecordIsSelectableUsing(fn (Order $record): bool => SendToSteadfastAction::canSend($record))
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->poll('30s')
            ->emptyStateIcon('heroicon-o-shopping-cart')
            ->emptyStateHeading('No orders here yet')
            ->emptyStateDescription('Orders placed on the website show up here automatically.');
    }
}
