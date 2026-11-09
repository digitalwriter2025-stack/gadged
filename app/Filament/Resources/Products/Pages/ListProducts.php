<?php

namespace App\Filament\Resources\Products\Pages;

use App\Enums\ProductStatus;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    /**
     * Products at or below this many units count as "low stock".
     */
    public const LOW_STOCK_THRESHOLD = 5;

    public function getSubheading(): string
    {
        return 'Manage your catalogue. Toggle "Featured" right from the list, or open a product to edit details.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon('heroicon-m-plus'),
        ];
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $statusCounts = Product::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $lowStock = fn (Builder $query): Builder => $query
            ->where('manage_stock', true)
            ->whereBetween('total_stock', [1, self::LOW_STOCK_THRESHOLD]);

        $outOfStock = fn (Builder $query): Builder => $query
            ->where('manage_stock', true)
            ->where('total_stock', '<=', 0);

        return [
            'all' => Tab::make('All')
                ->icon('heroicon-m-squares-2x2')
                ->badge($statusCounts->sum() ?: null)
                ->badgeColor('gray'),

            'active' => Tab::make('Active')
                ->badge($statusCounts->get(ProductStatus::Active->value) ?: null)
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', ProductStatus::Active)),

            'draft' => Tab::make('Draft')
                ->badge($statusCounts->get(ProductStatus::Draft->value) ?: null)
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', ProductStatus::Draft)),

            'archived' => Tab::make('Archived')
                ->badge($statusCounts->get(ProductStatus::Archived->value) ?: null)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', ProductStatus::Archived)),

            'low_stock' => Tab::make('Low stock')
                ->icon('heroicon-m-exclamation-triangle')
                ->badge($lowStock(Product::query())->count() ?: null)
                ->badgeColor('warning')
                ->modifyQueryUsing($lowStock),

            'out_of_stock' => Tab::make('Out of stock')
                ->icon('heroicon-m-archive-box-x-mark')
                ->badge($outOfStock(Product::query())->count() ?: null)
                ->badgeColor('danger')
                ->modifyQueryUsing($outOfStock),
        ];
    }
}
