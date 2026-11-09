<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Finance\Concerns;

use App\Enums\Permission;
use App\Services\FinanceReportService;
use App\Support\Money;
use App\Support\ReportPeriod;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

/**
 * Shared by the finance report widgets: they live only on the FinanceReport page
 * (not the dashboard) and read the period from its filters.
 */
trait InteractsWithFinanceReport
{
    use InteractsWithPageFilters;

    public static function isDiscovered(): bool
    {
        return false;
    }

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can(Permission::FinanceView->value);
    }

    protected function period(): ReportPeriod
    {
        return ReportPeriod::fromFilters($this->pageFilters);
    }

    protected function report(): FinanceReportService
    {
        return app(FinanceReportService::class);
    }

    /**
     * "৳12,500", or "-৳1,200" for a loss.
     */
    protected static function money(string $amount): string
    {
        $symbol = (string) config('shop.currency.symbol');

        return str_starts_with($amount, '-')
            ? '-'.Money::format(substr($amount, 1), $symbol)
            : Money::format($amount, $symbol);
    }
}
