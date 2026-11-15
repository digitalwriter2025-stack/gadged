<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ChecksDashboardAccess;
use App\Services\SalesReportService;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

/**
 * Revenue line with order count bars on a second axis.
 */
class RevenueChart extends ChartWidget
{
    use ChecksDashboardAccess;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Revenue & orders';

    protected ?string $description = 'Confirmed, processing, packed, shipped and delivered orders.';

    protected ?string $maxHeight = '320px';

    protected ?string $pollingInterval = null;

    public ?string $filter = SalesReportService::PERIOD_MONTH;

    public static function canView(): bool
    {
        return self::canViewSales();
    }

    /**
     * @return array<string, string>
     */
    protected function getFilters(): ?array
    {
        return [
            SalesReportService::PERIOD_WEEK => 'Last 7 days',
            SalesReportService::PERIOD_MONTH => 'Last 30 days',
            SalesReportService::PERIOD_YEAR => 'Last 12 months',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $trend = app(SalesReportService::class)->trend($this->filter ?? SalesReportService::PERIOD_MONTH);

        return [
            'labels' => $trend['labels'],
            'datasets' => [
                [
                    'type' => 'line',
                    'label' => 'Revenue',
                    'data' => $trend['revenue'],
                    'yAxisID' => 'y',
                    'borderColor' => '#ff5a36',
                    'pointBackgroundColor' => '#ff5a36',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 1.5,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 6,
                    'borderWidth' => 2.5,
                    'fill' => true,
                    'tension' => 0.35,
                    'order' => 1,
                ],
                [
                    'type' => 'bar',
                    'label' => 'Orders',
                    'data' => $trend['orders'],
                    'yAxisID' => 'y1',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.35)',
                    'hoverBackgroundColor' => 'rgba(139, 92, 246, 0.6)',
                    'borderWidth' => 0,
                    'borderRadius' => 6,
                    'maxBarThickness' => 28,
                    'order' => 2,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): RawJs
    {
        $currencySymbol = "'".addslashes((string) config('shop.currency.symbol'))."'";

        return RawJs::make(<<<JS
            {
                interaction: { mode: 'index', intersect: false },
                datasets: {
                    line: {
                        backgroundColor: ({ chart }) => {
                            const { ctx, chartArea } = chart;

                            if (! chartArea) {
                                return 'rgba(255, 90, 54, 0.15)';
                            }

                            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            gradient.addColorStop(0, 'rgba(255, 90, 54, 0.35)');
                            gradient.addColorStop(0.6, 'rgba(217, 70, 239, 0.08)');
                            gradient.addColorStop(1, 'rgba(217, 70, 239, 0)');

                            return gradient;
                        },
                    },
                },
                plugins: {
                    legend: { display: true, position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } },
                    tooltip: {
                        callbacks: {
                            label: (context) => context.dataset.yAxisID === 'y'
                                ? ' Revenue: ' + {$currencySymbol} + context.parsed.y.toLocaleString()
                                : ' Orders: ' + context.parsed.y,
                        },
                    },
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        position: 'left',
                        beginAtZero: true,
                        ticks: { precision: 0, callback: (value) => {$currencySymbol} + Number(value).toLocaleString() },
                    },
                    y1: {
                        position: 'right',
                        beginAtZero: true,
                        grid: { drawOnChartArea: false },
                        ticks: { precision: 0 },
                    },
                },
            }
        JS);
    }
}
