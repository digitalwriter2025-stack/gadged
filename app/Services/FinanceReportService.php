<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LedgerCategory;
use App\Enums\LedgerEntryType;
use App\Enums\OrderStatus;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\Money;
use App\Support\ReportPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Figures for the finance report: sales, earnings, pending and cancelled orders,
 * recorded income and expenses, and profit.
 *
 * Order counts ("sales", "pending", "cancelled") use the day the order was placed.
 * Earnings and profit use the day it was delivered, and only orders that are
 * still delivered count, so returned orders drop out. Product cost is the
 * order-time snapshot, falling back to the variant's current cost price.
 */
class FinanceReportService
{
    private const COST_EXPRESSION = 'COALESCE(order_items.unit_cost, product_variants.cost_price)';

    /**
     * Count and value of orders placed in the period, for every status.
     *
     * @return array<string, array{orders: int, amount: string}>
     */
    public function ordersByStatus(ReportPeriod $period): array
    {
        $rows = Order::query()
            ->where('created_at', '>=', $period->startUtc())
            ->where('created_at', '<', $period->endUtc())
            ->toBase()
            ->selectRaw('status, COUNT(*) as orders, SUM(grand_total) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return collect(OrderStatus::cases())
            ->mapWithKeys(fn (OrderStatus $status): array => [$status->value => [
                'orders' => (int) ($rows->get($status->value)?->orders ?? 0),
                'amount' => Money::of($rows->get($status->value)?->amount),
            ]])
            ->all();
    }

    /**
     * Headline sell / earn / pending / cancel figures.
     *
     * @return array{
     *     sales: array{orders: int, amount: string},
     *     awaiting_delivery: array{orders: int, amount: string},
     *     pending: array{orders: int, amount: string},
     *     cancelled: array{orders: int, amount: string},
     *     returned: array{orders: int, amount: string},
     *     earned: array{orders: int, amount: string},
     * }
     */
    public function orderSummary(ReportPeriod $period): array
    {
        $byStatus = $this->ordersByStatus($period);

        $total = fn (OrderStatus ...$statuses): array => [
            'orders' => array_sum(array_map(fn (OrderStatus $status): int => $byStatus[$status->value]['orders'], $statuses)),
            'amount' => Money::add(...array_map(fn (OrderStatus $status): string => $byStatus[$status->value]['amount'], $statuses)),
        ];

        $delivered = $this->deliveredSales($period);

        return [
            'sales' => $total(...OrderStatus::revenueStatuses()),
            'awaiting_delivery' => $total(OrderStatus::Confirmed, OrderStatus::Processing, OrderStatus::Packed, OrderStatus::Shipped),
            'pending' => $total(OrderStatus::Pending),
            'cancelled' => $total(OrderStatus::Cancelled),
            'returned' => $total(OrderStatus::Returned, OrderStatus::Refunded),
            'earned' => ['orders' => $delivered['orders'], 'amount' => $delivered['net_sales']],
        ];
    }

    /**
     * Orders delivered in the period and the cost of the products in them.
     *
     * @return array{orders: int, sales: string, refunds: string, net_sales: string, cost_of_goods: string, items_without_cost: int}
     */
    public function deliveredSales(ReportPeriod $period): array
    {
        $orders = $this->deliveredOrders($period)
            ->toBase()
            ->selectRaw('COUNT(*) as orders, SUM(grand_total) as sales, SUM(refunded_total) as refunds')
            ->first();

        $items = $this->deliveredItems($period)
            ->selectRaw('SUM((order_items.quantity - order_items.returned_quantity) * COALESCE('.self::COST_EXPRESSION.', 0)) as cost')
            ->selectRaw('SUM(CASE WHEN '.self::COST_EXPRESSION.' IS NULL THEN 1 ELSE 0 END) as items_without_cost')
            ->first();

        $sales = Money::of($orders?->sales);
        $refunds = Money::of($orders?->refunds);

        return [
            'orders' => (int) ($orders?->orders ?? 0),
            'sales' => $sales,
            'refunds' => $refunds,
            'net_sales' => Money::sub($sales, $refunds),
            'cost_of_goods' => Money::of($items?->cost),
            'items_without_cost' => (int) ($items?->items_without_cost ?? 0),
        ];
    }

    /**
     * Recorded ledger totals per category for entries dated within the period.
     *
     * @return array<string, string>
     */
    public function ledgerByCategory(ReportPeriod $period): array
    {
        return LedgerEntry::query()
            ->whereBetween('entry_date', [$period->start->toDateString(), $period->end->toDateString()])
            ->toBase()
            ->selectRaw('category, SUM(amount) as amount')
            ->groupBy('category')
            ->pluck('amount', 'category')
            ->map(fn (mixed $amount): string => Money::of((string) $amount))
            ->all();
    }

    /**
     * Profit & loss statement. Ledger lines are keyed by category value, largest first.
     *
     * @return array{
     *     delivered_orders: int,
     *     sales: string,
     *     refunds: string,
     *     net_sales: string,
     *     cost_of_goods: string,
     *     items_without_cost: int,
     *     gross_profit: string,
     *     other_income: array<string, string>,
     *     other_income_total: string,
     *     expenses: array<string, string>,
     *     expenses_total: string,
     *     total_income: string,
     *     net_profit: string,
     *     not_in_profit: array<string, string>,
     * }
     */
    public function profitAndLoss(ReportPeriod $period): array
    {
        $delivered = $this->deliveredSales($period);
        $ledger = $this->ledgerByCategory($period);

        $otherIncome = $this->ledgerLines($ledger, fn (LedgerCategory $category): bool => $category->affectsProfit() && $category->type() === LedgerEntryType::Income);
        $expenses = $this->ledgerLines($ledger, fn (LedgerCategory $category): bool => $category->affectsProfit() && $category->type() === LedgerEntryType::Expense);
        $notInProfit = $this->ledgerLines($ledger, fn (LedgerCategory $category): bool => ! $category->affectsProfit());

        $grossProfit = Money::sub($delivered['net_sales'], $delivered['cost_of_goods']);
        $otherIncomeTotal = Money::add(...array_values($otherIncome));
        $expensesTotal = Money::add(...array_values($expenses));

        return [
            'delivered_orders' => $delivered['orders'],
            'sales' => $delivered['sales'],
            'refunds' => $delivered['refunds'],
            'net_sales' => $delivered['net_sales'],
            'cost_of_goods' => $delivered['cost_of_goods'],
            'items_without_cost' => $delivered['items_without_cost'],
            'gross_profit' => $grossProfit,
            'other_income' => $otherIncome,
            'other_income_total' => $otherIncomeTotal,
            'expenses' => $expenses,
            'expenses_total' => $expensesTotal,
            'total_income' => Money::add($delivered['net_sales'], $otherIncomeTotal),
            'net_profit' => Money::sub(Money::add($grossProfit, $otherIncomeTotal), $expensesTotal),
            'not_in_profit' => $notInProfit,
        ];
    }

    /**
     * Income, expense and net profit per day, or per month for long periods.
     *
     * @return array{labels: list<string>, income: list<float>, expense: list<float>, profit: list<float>}
     */
    public function trend(ReportPeriod $period): array
    {
        $isMonthly = $period->isMonthly();
        $keyFormat = $isMonthly ? 'Y-m' : 'Y-m-d';
        $buckets = [];

        for ($cursor = $period->start; $cursor <= $period->end; $cursor = $isMonthly ? $cursor->addMonthNoOverflow()->startOfMonth() : $cursor->addDay()) {
            $buckets[$cursor->format($keyFormat)] = [
                'label' => $cursor->format($isMonthly ? 'M Y' : 'd M'),
                'income' => '0.00',
                'cost' => '0.00',
                'expense' => '0.00',
            ];
        }

        $costPerOrder = $this->deliveredItems($period)
            ->selectRaw('order_items.order_id, SUM((order_items.quantity - order_items.returned_quantity) * COALESCE('.self::COST_EXPRESSION.', 0)) as cost')
            ->groupBy('order_items.order_id')
            ->pluck('cost', 'order_id');

        $this->deliveredOrders($period)
            ->toBase()
            ->select(['id', 'delivered_at', 'grand_total', 'refunded_total'])
            ->lazyById(1000)
            ->each(function (object $order) use (&$buckets, $keyFormat, $costPerOrder): void {
                $key = CarbonImmutable::parse($order->delivered_at, 'UTC')->setTimezone($this->timezone())->format($keyFormat);

                if (isset($buckets[$key])) {
                    $buckets[$key]['income'] = Money::add($buckets[$key]['income'], Money::sub($order->grand_total, $order->refunded_total));
                    $buckets[$key]['cost'] = Money::add($buckets[$key]['cost'], (string) ($costPerOrder->get($order->id) ?? '0'));
                }
            });

        LedgerEntry::query()
            ->whereBetween('entry_date', [$period->start->toDateString(), $period->end->toDateString()])
            ->whereIn('category', $this->profitCategories())
            ->toBase()
            ->selectRaw('entry_date, type, SUM(amount) as amount')
            ->groupBy('entry_date', 'type')
            ->get()
            ->each(function (object $row) use (&$buckets, $keyFormat): void {
                $key = CarbonImmutable::parse($row->entry_date)->format($keyFormat);
                $column = $row->type === LedgerEntryType::Income->value ? 'income' : 'expense';

                if (isset($buckets[$key])) {
                    $buckets[$key][$column] = Money::add($buckets[$key][$column], (string) $row->amount);
                }
            });

        return [
            'labels' => array_column($buckets, 'label'),
            'income' => array_map(fn (array $bucket): float => (float) $bucket['income'], array_values($buckets)),
            'expense' => array_map(fn (array $bucket): float => (float) $bucket['expense'], array_values($buckets)),
            'profit' => array_map(
                fn (array $bucket): float => (float) Money::sub(Money::sub($bucket['income'], $bucket['cost']), $bucket['expense']),
                array_values($buckets),
            ),
        ];
    }

    /**
     * Profit / revenue as a percentage with one decimal, or null without revenue.
     */
    public static function margin(string $profit, string $revenue): ?float
    {
        if (! Money::isPositive($revenue)) {
            return null;
        }

        return round((float) bcdiv(bcmul(Money::of($profit), '100', 4), Money::of($revenue), 4), 1);
    }

    /**
     * Orders that were delivered in the period and have not been returned since.
     *
     * @return Builder<Order>
     */
    private function deliveredOrders(ReportPeriod $period): Builder
    {
        return Order::query()
            ->withStatus(OrderStatus::Delivered)
            ->where('delivered_at', '>=', $period->startUtc())
            ->where('delivered_at', '<', $period->endUtc());
    }

    private function deliveredItems(ReportPeriod $period): QueryBuilder
    {
        return OrderItem::query()
            ->toBase()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->where('orders.status', OrderStatus::Delivered->value)
            ->where('orders.delivered_at', '>=', $period->startUtc())
            ->where('orders.delivered_at', '<', $period->endUtc());
    }

    /**
     * Non-zero ledger totals for the matching categories, largest first.
     *
     * @param  array<string, string>  $ledger
     * @param  callable(LedgerCategory): bool  $matches
     * @return array<string, string>
     */
    private function ledgerLines(array $ledger, callable $matches): array
    {
        return collect($ledger)
            ->filter(fn (string $amount, string $category): bool => Money::isPositive($amount)
                && ($case = LedgerCategory::tryFrom($category)) !== null
                && $matches($case))
            ->sortByDesc(fn (string $amount): float => (float) $amount)
            ->all();
    }

    /**
     * @return list<string>
     */
    private function profitCategories(): array
    {
        return array_values(array_map(
            fn (LedgerCategory $category): string => $category->value,
            array_filter(LedgerCategory::cases(), fn (LedgerCategory $category): bool => $category->affectsProfit()),
        ));
    }

    private function timezone(): string
    {
        return (string) config('app.display_timezone', config('app.timezone'));
    }
}
