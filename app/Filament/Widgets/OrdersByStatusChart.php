<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Filament\Widgets\Concerns\ChecksDashboardAccess;
use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersByStatusChart extends ChartWidget
{
    use ChecksDashboardAccess;

    protected static ?int $sort = 3;

    protected ?string $heading = 'Orders by status';

    protected ?string $description = 'All orders placed in the last 30 days.';

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = null;

    /**
     * Chart colour per status, matching the badge colours used in the panel.
     */
    private const STATUS_COLORS = [
        'pending' => '#f59e0b',
        'confirmed' => '#38bdf8',
        'processing' => '#3b82f6',
        'packed' => '#6366f1',
        'shipped' => '#f97316',
        'delivered' => '#10b981',
        'cancelled' => '#ef4444',
        'returned' => '#f43f5e',
        'refunded' => '#94a3b8',
    ];

    public static function canView(): bool
    {
        return self::canViewSales();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $counts = Order::query()
            ->where('created_at', '>=', now()->subDays(30))
            ->toBase()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statuses = array_values(array_filter(
            OrderStatus::cases(),
            fn (OrderStatus $status): bool => (int) ($counts[$status->value] ?? 0) > 0,
        ));

        if ($statuses === []) {
            return [];
        }

        return [
            'labels' => array_map(fn (OrderStatus $status): string => $status->getLabel(), $statuses),
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => array_map(fn (OrderStatus $status): int => (int) $counts[$status->value], $statuses),
                    'backgroundColor' => array_map(fn (OrderStatus $status): string => self::STATUS_COLORS[$status->value], $statuses),
                    'borderWidth' => 0,
                    'hoverOffset' => 8,
                    'borderRadius' => 6,
                    'spacing' => count($statuses) > 1 ? 3 : 0,
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
            'cutout' => '74%',
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
