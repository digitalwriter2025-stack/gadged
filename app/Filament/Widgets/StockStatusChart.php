<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\ProductStatus;
use App\Enums\StockStatus;
use App\Filament\Widgets\Concerns\ChecksDashboardAccess;
use App\Models\Product;
use Filament\Widgets\ChartWidget;

class StockStatusChart extends ChartWidget
{
    use ChecksDashboardAccess;

    protected static ?int $sort = 4;

    protected ?string $heading = 'Stock availability';

    protected ?string $description = 'Active products by stock status.';

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = null;

    private const STATUS_COLORS = [
        'in_stock' => '#10b981',
        'out_of_stock' => '#ef4444',
        'pre_order' => '#3b82f6',
        'upcoming' => '#f59e0b',
    ];

    public static function canView(): bool
    {
        return self::canViewCatalog();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $counts = Product::query()
            ->where('status', ProductStatus::Active)
            ->toBase()
            ->selectRaw('stock_status, COUNT(*) as total')
            ->groupBy('stock_status')
            ->pluck('total', 'stock_status');

        $statuses = array_values(array_filter(
            StockStatus::cases(),
            fn (StockStatus $status): bool => (int) ($counts[$status->value] ?? 0) > 0,
        ));

        if ($statuses === []) {
            return [];
        }

        return [
            'labels' => array_map(fn (StockStatus $status): string => $status->getLabel(), $statuses),
            'datasets' => [
                [
                    'label' => 'Products',
                    'data' => array_map(fn (StockStatus $status): int => (int) $counts[$status->value], $statuses),
                    'backgroundColor' => array_map(fn (StockStatus $status): string => self::STATUS_COLORS[$status->value], $statuses),
                    'borderWidth' => 0,
                    'hoverOffset' => 8,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'cutout' => '68%',
            'plugins' => [
                'legend' => ['position' => 'right', 'labels' => ['usePointStyle' => true, 'boxWidth' => 8]],
            ],
            'scales' => [
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
        ];
    }
}
