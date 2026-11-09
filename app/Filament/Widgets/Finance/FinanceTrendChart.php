<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Finance;

use App\Filament\Widgets\Finance\Concerns\InteractsWithFinanceReport;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

/**
 * Income and expense bars with a net profit line, per day or per month.
 */
class FinanceTrendChart extends ChartWidget
{
    use InteractsWithFinanceReport;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Income vs expense';

    protected ?string $maxHeight = '320px';

    protected ?string $pollingInterval = null;

    public function getDescription(): ?string
    {
        return $this->period()->isMonthly()
            ? 'Per month. Income is delivered sales plus other income; profit also deducts product cost.'
            : 'Per day. Income is delivered sales plus other income; profit also deducts product cost.';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $trend = $this->report()->trend($this->period());

        return [
            'labels' => $trend['labels'],
            'datasets' => [
                [
                    'type' => 'line',
                    'label' => 'Net profit',
                    'data' => $trend['profit'],
                    'borderColor' => '#8b5cf6',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.12)',
                    'pointBackgroundColor' => '#8b5cf6',
                    'pointRadius' => 3,
                    'borderWidth' => 2.5,
                    'tension' => 0.35,
                    'order' => 1,
                ],
                [
                    'type' => 'bar',
                    'label' => 'Income',
                    'data' => $trend['income'],
                    'backgroundColor' => 'rgba(16, 185, 129, 0.55)',
                    'borderRadius' => 6,
                    'maxBarThickness' => 26,
                    'order' => 2,
                ],
                [
                    'type' => 'bar',
                    'label' => 'Expense',
                    'data' => $trend['expense'],
                    'backgroundColor' => 'rgba(244, 63, 94, 0.55)',
                    'borderRadius' => 6,
                    'maxBarThickness' => 26,
                    'order' => 3,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): RawJs
    {
        $currencySymbol = "'".addslashes((string) config('shop.currency.symbol'))."'";

        return RawJs::make(<<<JS
            {
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: true, position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } },
                    tooltip: {
                        callbacks: {
                            label: (context) => ' ' + context.dataset.label + ': ' + {$currencySymbol} + context.parsed.y.toLocaleString(),
                        },
                    },
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { ticks: { callback: (value) => {$currencySymbol} + Number(value).toLocaleString() } },
                },
            }
        JS);
    }
}
