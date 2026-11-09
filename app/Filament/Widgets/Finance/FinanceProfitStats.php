<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Finance;

use App\Filament\Widgets\Finance\Concerns\InteractsWithFinanceReport;
use App\Services\FinanceReportService;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Income, expense, gross and net profit cards for the selected period.
 */
class FinanceProfitStats extends StatsOverviewWidget
{
    use InteractsWithFinanceReport;

    protected static ?int $sort = 2;

    protected ?string $pollingInterval = null;

    protected ?string $heading = 'Income & expense · আয়-ব্যয়';

    /**
     * @return list<Stat>
     */
    protected function getStats(): array
    {
        $statement = $this->report()->profitAndLoss($this->period());
        $netMargin = FinanceReportService::margin($statement['net_profit'], $statement['total_income']);
        $grossMargin = FinanceReportService::margin($statement['gross_profit'], $statement['net_sales']);
        $isLoss = str_starts_with($statement['net_profit'], '-');

        return [
            Stat::make('Total income · মোট আয়', self::money($statement['total_income']))
                ->icon('heroicon-o-arrow-down-left')
                ->extraAttributes(['data-accent' => 'teal'])
                ->description(sprintf('Sales %s + other %s', self::money($statement['net_sales']), self::money($statement['other_income_total'])))
                ->color('success'),

            Stat::make('Total expense · মোট ব্যয়', self::money($statement['expenses_total']))
                ->icon('heroicon-o-arrow-up-right')
                ->extraAttributes(['data-accent' => 'orange'])
                ->description('Rent, salary, ads, courier and other running costs')
                ->color('danger'),

            Stat::make('Gross profit · মোট লাভ', self::money($statement['gross_profit']))
                ->icon('heroicon-o-scale')
                ->extraAttributes(['data-accent' => 'violet'])
                ->description(sprintf(
                    'After %s product cost%s',
                    self::money($statement['cost_of_goods']),
                    $grossMargin === null ? '' : " · {$grossMargin}% margin",
                ))
                ->color('info'),

            Stat::make($isLoss ? 'Net loss · নিট ক্ষতি' : 'Net profit · নিট লাভ', self::money($statement['net_profit']))
                ->icon($isLoss ? 'heroicon-o-arrow-trending-down' : 'heroicon-o-arrow-trending-up')
                ->extraAttributes(['data-accent' => $isLoss ? 'rose' : 'emerald'])
                ->description($netMargin === null ? 'No income in this period' : "{$netMargin}% of total income")
                ->descriptionIcon($isLoss ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                ->color(Money::isZero($statement['net_profit']) ? 'gray' : ($isLoss ? 'danger' : 'success')),
        ];
    }
}
