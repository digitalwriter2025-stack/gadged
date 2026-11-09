<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Finance;

use App\Enums\LedgerCategory;
use App\Enums\LedgerEntryType;
use App\Filament\Widgets\Finance\Concerns\InteractsWithFinanceReport;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

/**
 * Where the money went: every recorded expense in the period by category.
 */
class ExpenseBreakdownChart extends ChartWidget
{
    use InteractsWithFinanceReport;

    private const COLORS = [
        '#f43f5e', '#f97316', '#f59e0b', '#10b981', '#14b8a6',
        '#3b82f6', '#8b5cf6', '#ec4899', '#64748b', '#84cc16', '#06b6d4',
    ];

    protected static ?int $sort = 5;

    protected ?string $heading = 'Expenses by category · খরচের খাত';

    protected ?string $description = 'All recorded expenses, including stock purchases and owner withdrawals.';

    protected ?string $maxHeight = '300px';

    protected ?string $pollingInterval = null;

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $ledger = $this->report()->ledgerByCategory($this->period());

        $expenses = collect(LedgerCategory::forType(LedgerEntryType::Expense))
            ->mapWithKeys(fn (LedgerCategory $category): array => [$category->getLabel() => (float) ($ledger[$category->value] ?? 0)])
            ->filter(fn (float $amount): bool => $amount > 0)
            ->sortDesc();

        return [
            'labels' => $expenses->keys()->all(),
            'datasets' => [
                [
                    'data' => $expenses->values()->all(),
                    'backgroundColor' => array_slice(self::COLORS, 0, max($expenses->count(), 1)),
                    'borderWidth' => 0,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): RawJs
    {
        $currencySymbol = "'".addslashes((string) config('shop.currency.symbol'))."'";

        return RawJs::make(<<<JS
            {
                cutout: '62%',
                scales: { x: { display: false }, y: { display: false } },
                plugins: {
                    legend: { display: true, position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } },
                    tooltip: {
                        callbacks: {
                            label: (context) => ' ' + context.label + ': ' + {$currencySymbol} + context.parsed.toLocaleString(),
                        },
                    },
                },
            }
        JS);
    }
}
