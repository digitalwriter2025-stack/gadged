<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Tables;

use App\Enums\Permission;
use App\Enums\ProductStatus;
use App\Enums\StockStatus;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HtmlString;

class ProductsTable
{
    /**
     * Shown for products that have no main image yet.
     */
    public const PLACEHOLDER_IMAGE = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='1.4'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A1.5 1.5 0 0 0 21.75 19.5V4.5A1.5 1.5 0 0 0 20.25 3H3.75A1.5 1.5 0 0 0 2.25 4.5v15A1.5 1.5 0 0 0 3.75 21Z'/%3E%3C/svg%3E";

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['category', 'brand', 'media']))
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->state(fn (Product $record): ?string => $record->getFirstMediaUrl('main') ?: null)
                    ->defaultImageUrl(self::PLACEHOLDER_IMAGE)
                    ->square()
                    ->imageSize(56)
                    ->extraImgAttributes(['class' => 'gx-thumb', 'loading' => 'lazy']),

                TextColumn::make('name')
                    ->label('Product')
                    ->searchable(['name', 'sku', 'model'])
                    ->sortable()
                    ->weight('semibold')
                    ->wrap()
                    ->lineClamp(2)
                    ->description(fn (Product $record): string => collect(['SKU: '.$record->sku, $record->model ? 'Model: '.$record->model : null])->filter()->implode('  ·  ')),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('info')
                    ->description(fn (Product $record): ?string => $record->brand?->name)
                    ->sortable(),

                TextColumn::make('min_price')
                    ->label('Price')
                    ->money('BDT')
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->description(fn (Product $record): ?HtmlString => (float) $record->regular_price > (float) $record->min_price
                        ? new HtmlString('<s class="gx-price-was">BDT '.number_format((float) $record->regular_price, 2).'</s>'
                            .($record->discount_percent ? ' <span class="gx-price-off">-'.$record->discount_percent.'%</span>' : ''))
                        : null),

                TextColumn::make('total_stock')
                    ->label('Stock')
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn (int $state, Product $record): string => match (true) {
                        ! $record->manage_stock => 'Not tracked',
                        $state <= 0 => 'Out of stock',
                        $state <= ListProducts::LOW_STOCK_THRESHOLD => "Only {$state} left",
                        default => "{$state} in stock",
                    })
                    ->color(fn (int $state, Product $record): string => match (true) {
                        ! $record->manage_stock => 'gray',
                        $state <= 0 => 'danger',
                        $state <= ListProducts::LOW_STOCK_THRESHOLD => 'warning',
                        default => 'success',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                ToggleColumn::make('is_featured')
                    ->label('Featured')
                    ->onColor('primary')
                    ->disabled(fn (Product $record): bool => ! auth()->user()?->can('update', $record))
                    ->alignCenter(),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('brand_id')
                    ->relationship('brand', 'name')
                    ->label('Brand')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('stock_status')
                    ->label('Availability')
                    ->options(StockStatus::class),
                TernaryFilter::make('is_featured')
                    ->label('Featured'),
            ])
            ->filtersFormColumns(2)
            ->recordActions([
                EditAction::make()->iconButton()->tooltip('Edit product'),
                ActionGroup::make([
                    Action::make('viewOnSite')
                        ->label('View on site')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn (Product $record): string => route('store.products.show', $record))
                        ->openUrlInNewTab()
                        ->visible(fn (Product $record): bool => $record->isPublished()),
                    DeleteAction::make(),
                ])->tooltip('More actions'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::statusBulkAction(ProductStatus::Active, 'Mark as active', 'heroicon-o-eye'),
                    self::statusBulkAction(ProductStatus::Draft, 'Move to draft', 'heroicon-o-eye-slash'),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->emptyStateIcon('heroicon-o-shopping-bag')
            ->emptyStateHeading('No products found')
            ->emptyStateDescription('Try another tab or filter, or add your first product.')
            ->emptyStateActions([
                CreateAction::make()->label('New product'),
            ]);
    }

    private static function statusBulkAction(ProductStatus $status, string $label, string $icon): BulkAction
    {
        return BulkAction::make('mark'.$status->name)
            ->label($label)
            ->icon($icon)
            ->requiresConfirmation()
            ->visible(fn (): bool => (bool) auth()->user()?->can(Permission::ProductsUpdate->value))
            ->action(function (Collection $records, BulkAction $action) use ($status): void {
                $records->each(fn (Product $product) => $product->update(['status' => $status]));

                $action->success();
            })
            ->deselectRecordsAfterCompletion()
            ->successNotificationTitle('Products updated');
    }
}
