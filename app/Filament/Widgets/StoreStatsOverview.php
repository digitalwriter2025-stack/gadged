<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Enums\StockStatus;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Widgets\Concerns\ChecksDashboardAccess;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\SalesReportService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * KPI cards at the top of the dashboard. Sales cards need order access,
 * catalog cards need product access.
 */
class StoreStatsOverview extends StatsOverviewWidget
{
    use ChecksDashboardAccess;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    public static function canView(): bool
    {
        return self::canViewSales() || self::canViewCatalog();
    }

    /**
     * @return list<Stat>
     */
    protected function getStats(): array
    {
        return [
            ...(self::canViewSales() ? $this->salesStats() : []),
            ...(self::canViewCatalog() ? $this->catalogStats() : []),
        ];
    }

    /**
     * @return list<Stat>
     */
    private function salesStats(): array
    {
        $report = app(SalesReportService::class);
        $summary = $report->summary(30);
        $trend = $report->trend(SalesReportService::PERIOD_MONTH);

        $currencySymbol = (string) config('shop.currency.symbol');
        $pendingOrders = Order::query()->withStatus(OrderStatus::Pending)->count();
        $newCustomers = User::query()->customers()->where('created_at', '>=', now()->subDays(30))->count();

        return [
            $this->withTrend(
                Stat::make('Revenue · 30 days', $currencySymbol.number_format($summary['revenue'])),
                SalesReportService::percentChange($summary['revenue'], $summary['previous_revenue']),
            )
                ->icon('heroicon-o-banknotes')
                ->extraAttributes(['data-accent' => 'orange'])
                ->chart($trend['revenue']),

            $this->withTrend(
                Stat::make('Orders · 30 days', number_format($summary['orders'])),
                SalesReportService::percentChange($summary['orders'], $summary['previous_orders']),
            )
                ->icon('heroicon-o-shopping-cart')
                ->extraAttributes(['data-accent' => 'blue'])
                ->chart($trend['orders']),

            Stat::make('Pending orders', number_format($pendingOrders))
                ->icon('heroicon-o-clock')
                ->extraAttributes(['data-accent' => $pendingOrders > 0 ? 'amber' : 'emerald'])
                ->description($pendingOrders > 0 ? 'Waiting for confirmation' : 'All caught up')
                ->descriptionIcon($pendingOrders > 0 ? 'heroicon-m-exclamation-circle' : 'heroicon-m-check-circle')
                ->color($pendingOrders > 0 ? 'warning' : 'success'),

            Stat::make('New customers · 30 days', number_format($newCustomers))
                ->icon('heroicon-o-user-plus')
                ->extraAttributes(['data-accent' => 'violet'])
                ->description(number_format(User::query()->customers()->count()).' customers in total')
                ->color('info'),
        ];
    }

    /**
     * @return list<Stat>
     */
    private function catalogStats(): array
    {
        $activeProducts = Product::query()->where('status', ProductStatus::Active)->count();
        $totalProducts = Product::query()->count();
        $outOfStock = Product::query()
            ->where('status', ProductStatus::Active)
            ->where('stock_status', StockStatus::OutOfStock)
            ->count();

        return [
            Stat::make('Active products', number_format($activeProducts))
                ->icon('heroicon-o-cube')
                ->extraAttributes(['data-accent' => 'teal'])
                ->url(ProductResource::getUrl())
                ->description(number_format($totalProducts - $activeProducts).' draft or archived')
                ->color('primary'),

            Stat::make('Out of stock', number_format($outOfStock))
                ->icon('heroicon-o-archive-box-x-mark')
                ->extraAttributes(['data-accent' => $outOfStock > 0 ? 'rose' : 'emerald'])
                ->url(ProductResource::getUrl())
                ->description($outOfStock > 0 ? 'Active products to restock' : 'Everything is in stock')
                ->descriptionIcon($outOfStock > 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-check-circle')
                ->color($outOfStock > 0 ? 'danger' : 'success'),
        ];
    }

    private function withTrend(Stat $stat, ?float $percentChange): Stat
    {
        if ($percentChange === null) {
            return $stat->description('No data for previous 30 days')->color('gray');
        }

        $isUp = $percentChange >= 0;

        return $stat
            ->description(($isUp ? '+' : '').$percentChange.'% vs previous 30 days')
            ->descriptionIcon($isUp ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($isUp ? 'success' : 'danger');
    }
}
