<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\ProductStatus;
use App\Filament\Widgets\Concerns\ChecksDashboardAccess;
use App\Models\Category;
use Filament\Widgets\ChartWidget;

class ProductsByCategoryChart extends ChartWidget
{
    use ChecksDashboardAccess;

    protected static ?int $sort = 5;

    protected ?string $heading = 'Products by category';

    protected ?string $description = 'Top 8 categories by active products.';

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
        $categories = Category::query()
            ->withCount(['products' => fn ($query) => $query->where('status', ProductStatus::Active)])
            ->orderByDesc('products_count')
            ->limit(8)
            ->get(['id', 'name'])
            ->filter(fn (Category $category): bool => $category->products_count > 0);

        if ($categories->isEmpty()) {
            return [];
        }

        return [
            'labels' => $categories->pluck('name')->all(),
            'datasets' => [
                [
                    'label' => 'Active products',
                    'data' => $categories->pluck('products_count')->all(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
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
