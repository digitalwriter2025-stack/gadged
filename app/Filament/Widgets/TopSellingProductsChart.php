<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ChecksDashboardAccess;
use App\Filament\Widgets\Concerns\UsesChartPalette;
use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Str;

class TopSellingProductsChart extends ChartWidget
{
    use ChecksDashboardAccess;
    use UsesChartPalette;

    protected static ?int $sort = 6;

    protected ?string $heading = 'Best sellers';

    protected ?string $description = 'Top 8 products by units sold.';

    protected ?string $maxHeight = '300px';

    protected ?string $pollingInterval = null;

    public static function canView(): bool
    {
        return self::canViewCatalog();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $products = Product::query()
            ->where('sales_count', '>', 0)
            ->orderByDesc('sales_count')
            ->limit(8)
            ->get(['id', 'name', 'sales_count']);

        if ($products->isEmpty()) {
            return [];
        }

        return [
            'labels' => $products->map(fn (Product $product): string => Str::limit($product->name, 28))->all(),
            'datasets' => [
                [
                    'label' => 'Units sold',
                    'data' => $products->pluck('sales_count')->all(),
                    'backgroundColor' => $this->barColors($products->count()),
                    'borderRadius' => 6,
                    'maxBarThickness' => 22,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => ['legend' => ['display' => false]],
            'scales' => [
                'x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
                'y' => ['grid' => ['display' => false]],
            ],
        ];
    }
}
