<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Finance;

use App\Enums\LedgerCategory;
use App\Filament\Widgets\Finance\Concerns\InteractsWithFinanceReport;
use App\Support\Money;
use Filament\Widgets\Widget;

/**
 * Line-by-line profit & loss statement for the selected period.
 */
class ProfitLossStatement extends Widget
{
    use InteractsWithFinanceReport;

    protected static ?int $sort = 4;

    protected string $view = 'filament.widgets.finance.profit-loss-statement';

    public function getPeriodLabel(): string
    {
        return $this->period()->label();
    }

    /**
     * @return array{
     *     rows: list<array{kind: string, label: string, amount?: string, tone?: string}>,
     *     not_in_profit: list<array{label: string, amount: string}>,
     *     items_without_cost: int,
     * }
     */
    public function getStatement(): array
    {
        $statement = $this->report()->profitAndLoss($this->period());
        $isLoss = str_starts_with($statement['net_profit'], '-');

        $rows = [
            ['kind' => 'heading', 'label' => 'Sales · বিক্রি'],
            ['kind' => 'line', 'label' => "Delivered orders ({$statement['delivered_orders']})", 'amount' => self::money($statement['sales'])],
        ];

        if (Money::isPositive($statement['refunds'])) {
            $rows[] = ['kind' => 'line', 'label' => 'Less: refunds', 'amount' => '− '.self::money($statement['refunds']), 'tone' => 'negative'];
        }

        $rows[] = ['kind' => 'subtotal', 'label' => 'Net sales', 'amount' => self::money($statement['net_sales'])];
        $rows[] = ['kind' => 'line', 'label' => 'Less: cost of products sold', 'amount' => '− '.self::money($statement['cost_of_goods']), 'tone' => 'negative'];
        $rows[] = ['kind' => 'subtotal', 'label' => 'Gross profit', 'amount' => self::money($statement['gross_profit'])];

        if ($statement['other_income'] !== []) {
            $rows[] = ['kind' => 'heading', 'label' => 'Other income · অন্যান্য আয়'];

            foreach ($statement['other_income'] as $category => $amount) {
                $rows[] = ['kind' => 'line', 'label' => self::categoryLabel($category), 'amount' => '+ '.self::money($amount), 'tone' => 'positive'];
            }
        }

        $rows[] = ['kind' => 'heading', 'label' => 'Operating expenses · খরচ'];

        foreach ($statement['expenses'] as $category => $amount) {
            $rows[] = ['kind' => 'line', 'label' => self::categoryLabel($category), 'amount' => '− '.self::money($amount), 'tone' => 'negative'];
        }

        if ($statement['expenses'] === []) {
            $rows[] = ['kind' => 'empty', 'label' => 'No expenses recorded in this period'];
        }

        $rows[] = ['kind' => 'subtotal', 'label' => 'Total operating expenses', 'amount' => '− '.self::money($statement['expenses_total'])];
        $rows[] = [
            'kind' => 'total',
            'label' => $isLoss ? 'Net loss · নিট ক্ষতি' : 'Net profit · নিট লাভ',
            'amount' => self::money($statement['net_profit']),
            'tone' => $isLoss ? 'negative' : 'positive',
        ];

        return [
            'rows' => $rows,
            'not_in_profit' => collect($statement['not_in_profit'])
                ->map(fn (string $amount, string $category): array => ['label' => self::categoryLabel($category), 'amount' => self::money($amount)])
                ->values()
                ->all(),
            'items_without_cost' => $statement['items_without_cost'],
        ];
    }

    private static function categoryLabel(string $category): string
    {
        return LedgerCategory::tryFrom($category)?->getLabel() ?? $category;
    }
}
