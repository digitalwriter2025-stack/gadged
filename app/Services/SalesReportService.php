<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use Carbon\CarbonImmutable;

/**
 * Revenue and order figures for the admin dashboard.
 *
 * Only orders in OrderStatus::revenueStatuses() count. Timestamps are stored in UTC
 * and bucketed in app.display_timezone so a "day" matches the shop's local day.
 */
class SalesReportService
{
    public const PERIOD_WEEK = 'week';

    public const PERIOD_MONTH = 'month';

    public const PERIOD_YEAR = 'year';

    /**
     * Revenue and order count per bucket: daily for week/month, monthly for year.
     *
     * @return array{labels: list<string>, revenue: list<float>, orders: list<int>}
     */
    public function trend(string $period): array
    {
        $now = $this->now();
        $isYearly = $period === self::PERIOD_YEAR;

        $start = match ($period) {
            self::PERIOD_WEEK => $now->subDays(6)->startOfDay(),
            self::PERIOD_YEAR => $now->subMonths(11)->startOfMonth(),
            default => $now->subDays(29)->startOfDay(),
        };

        $buckets = [];

        for ($cursor = $start; $cursor <= $now; $cursor = $isYearly ? $cursor->addMonth() : $cursor->addDay()) {
            $buckets[$cursor->format($isYearly ? 'Y-m' : 'Y-m-d')] = [
                'label' => $cursor->format($isYearly ? 'M Y' : 'd M'),
                'revenue' => 0.0,
                'orders' => 0,
            ];
        }

        Order::query()
            ->revenue()
            ->where('created_at', '>=', $start->utc())
            ->toBase()
            ->select(['id', 'created_at', 'grand_total'])
            ->lazyById(1000)
            ->each(function (object $order) use (&$buckets, $isYearly): void {
                $key = CarbonImmutable::parse($order->created_at, 'UTC')
                    ->setTimezone($this->timezone())
                    ->format($isYearly ? 'Y-m' : 'Y-m-d');

                if (isset($buckets[$key])) {
                    $buckets[$key]['revenue'] += (float) $order->grand_total;
                    $buckets[$key]['orders']++;
                }
            });

        return [
            'labels' => array_column($buckets, 'label'),
            'revenue' => array_map(fn (float $revenue): float => round($revenue, 2), array_column($buckets, 'revenue')),
            'orders' => array_column($buckets, 'orders'),
        ];
    }

    /**
     * Revenue and order count for the last $days days and the $days before that.
     *
     * @return array{revenue: float, orders: int, previous_revenue: float, previous_orders: int}
     */
    public function summary(int $days = 30): array
    {
        $currentStart = $this->now()->subDays($days - 1)->startOfDay();
        $previousStart = $currentStart->subDays($days);

        $totals = Order::query()
            ->revenue()
            ->where('created_at', '>=', $previousStart->utc())
            ->toBase()
            ->selectRaw('created_at >= ? as is_current, SUM(grand_total) as revenue, COUNT(*) as orders', [$currentStart->utc()])
            ->groupBy('is_current')
            ->get()
            ->keyBy(fn (object $row): int => (int) $row->is_current);

        return [
            'revenue' => (float) ($totals->get(1)?->revenue ?? 0),
            'orders' => (int) ($totals->get(1)?->orders ?? 0),
            'previous_revenue' => (float) ($totals->get(0)?->revenue ?? 0),
            'previous_orders' => (int) ($totals->get(0)?->orders ?? 0),
        ];
    }

    /**
     * Percentage change from $previous to $current, or null when there is no baseline.
     */
    public static function percentChange(float $current, float $previous): ?float
    {
        if ($previous <= 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function now(): CarbonImmutable
    {
        return CarbonImmutable::now($this->timezone());
    }

    private function timezone(): string
    {
        return (string) config('app.display_timezone', config('app.timezone'));
    }
}
