<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\LedgerCategory;
use App\Enums\OrderStatus;
use App\Enums\StaffRole;
use App\Filament\Pages\FinanceReport;
use App\Filament\Widgets\Finance\ExpenseBreakdownChart;
use App\Filament\Widgets\Finance\FinanceOrderStats;
use App\Filament\Widgets\Finance\FinanceProfitStats;
use App\Filament\Widgets\Finance\FinanceTrendChart;
use App\Filament\Widgets\Finance\ProfitLossStatement;
use App\Filament\Widgets\StoreStatsOverview;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\FinanceReportService;
use App\Support\ReportPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FinanceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_report_renders_its_widgets_for_super_admin(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->get(FinanceReport::getUrl())
            ->assertSuccessful()
            ->assertSee('Finance Report')
            ->assertSeeLivewire(FinanceOrderStats::class)
            ->assertSeeLivewire(FinanceProfitStats::class)
            ->assertSeeLivewire(FinanceTrendChart::class)
            ->assertSeeLivewire(ProfitLossStatement::class)
            ->assertSeeLivewire(ExpenseBreakdownChart::class);
    }

    public function test_finance_widgets_stay_off_the_main_dashboard(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->get('/admin')
            ->assertSuccessful()
            ->assertSeeLivewire(StoreStatsOverview::class)
            ->assertDontSeeLivewire(FinanceOrderStats::class)
            ->assertDontSeeLivewire(ProfitLossStatement::class);
    }

    public function test_admin_can_open_the_report_but_staff_without_finance_access_cannot(): void
    {
        $this->actingAs(User::factory()->staff(StaffRole::Admin)->create())
            ->get(FinanceReport::getUrl())
            ->assertSuccessful();

        $this->actingAs(User::factory()->staff(StaffRole::Manager)->create())
            ->get(FinanceReport::getUrl())
            ->assertForbidden();
    }

    public function test_order_summary_separates_sales_pending_cancelled_and_earned(): void
    {
        Order::factory()->status(OrderStatus::Pending)->create(['grand_total' => '1000.00']);
        Order::factory()->status(OrderStatus::Confirmed)->create(['grand_total' => '2000.00']);
        Order::factory()->status(OrderStatus::Delivered)->create(['grand_total' => '3000.00', 'delivered_at' => now()]);
        Order::factory()->status(OrderStatus::Cancelled)->create(['grand_total' => '500.00']);
        Order::factory()->status(OrderStatus::Returned)->create(['grand_total' => '800.00', 'delivered_at' => now()]);
        // Placed long ago but delivered now: earned in this period, not a sale of it.
        Order::factory()->status(OrderStatus::Delivered)->create(['grand_total' => '1500.00', 'created_at' => now()->subMonths(2), 'delivered_at' => now()]);
        Order::factory()->status(OrderStatus::Delivered)->create(['grand_total' => '9999.00', 'created_at' => now()->subMonths(2), 'delivered_at' => now()->subMonths(2)]);

        $summary = app(FinanceReportService::class)->orderSummary(ReportPeriod::fromFilters(['period' => ReportPeriod::LAST_7_DAYS]));

        $this->assertSame(['orders' => 2, 'amount' => '5000.00'], $summary['sales']);
        $this->assertSame(['orders' => 1, 'amount' => '2000.00'], $summary['awaiting_delivery']);
        $this->assertSame(['orders' => 1, 'amount' => '1000.00'], $summary['pending']);
        $this->assertSame(['orders' => 1, 'amount' => '500.00'], $summary['cancelled']);
        $this->assertSame(['orders' => 1, 'amount' => '800.00'], $summary['returned']);
        $this->assertSame(['orders' => 2, 'amount' => '4500.00'], $summary['earned']);
    }

    public function test_profit_and_loss_deducts_product_cost_and_running_expenses_only(): void
    {
        $product = Product::factory()->create();
        $variant = $product->defaultVariant()->firstOrFail();
        $variant->forceFill(['cost_price' => '1500.00'])->save();

        $order = Order::factory()->status(OrderStatus::Delivered)->create(['grand_total' => '10060.00', 'delivered_at' => now()]);
        $this->addItem($order, quantity: 2, unitCost: '3000.00');
        $this->addItem($order, quantity: 1, variantId: $variant->id);
        $this->addItem($order, quantity: 1);

        LedgerEntry::factory()->category(LedgerCategory::Rent)->create(['amount' => '2000.00']);
        LedgerEntry::factory()->category(LedgerCategory::Marketing)->create(['amount' => '500.00']);
        LedgerEntry::factory()->category(LedgerCategory::StockPurchase)->create(['amount' => '50000.00']);
        LedgerEntry::factory()->category(LedgerCategory::ServiceIncome)->create(['amount' => '700.00']);
        LedgerEntry::factory()->category(LedgerCategory::Investment)->create(['amount' => '100000.00']);
        LedgerEntry::factory()->category(LedgerCategory::Rent)->create(['amount' => '9000.00', 'entry_date' => now()->subMonths(3)->toDateString()]);

        $statement = app(FinanceReportService::class)->profitAndLoss(ReportPeriod::fromFilters(['period' => ReportPeriod::LAST_7_DAYS]));

        $this->assertSame(1, $statement['delivered_orders']);
        $this->assertSame('10060.00', $statement['net_sales']);
        $this->assertSame('7500.00', $statement['cost_of_goods']);
        $this->assertSame(1, $statement['items_without_cost']);
        $this->assertSame('2560.00', $statement['gross_profit']);
        $this->assertSame(['service_income' => '700.00'], $statement['other_income']);
        $this->assertSame(['rent' => '2000.00', 'marketing' => '500.00'], $statement['expenses']);
        $this->assertSame('10760.00', $statement['total_income']);
        $this->assertSame('760.00', $statement['net_profit']);
        $this->assertSame(['investment' => '100000.00', 'stock_purchase' => '50000.00'], $statement['not_in_profit']);
    }

    public function test_trend_puts_income_expense_and_profit_on_the_local_day(): void
    {
        $order = Order::factory()->status(OrderStatus::Delivered)->create(['grand_total' => '1000.00', 'delivered_at' => now()]);
        $this->addItem($order, quantity: 1, unitCost: '600.00');
        LedgerEntry::factory()->category(LedgerCategory::Courier)->create(['amount' => '100.00']);

        $trend = app(FinanceReportService::class)->trend(ReportPeriod::fromFilters(['period' => ReportPeriod::LAST_7_DAYS]));

        $this->assertCount(7, $trend['labels']);
        $this->assertSame(1000.0, end($trend['income']));
        $this->assertSame(100.0, end($trend['expense']));
        $this->assertSame(300.0, end($trend['profit']));

        $yearly = app(FinanceReportService::class)->trend(ReportPeriod::fromFilters(['period' => ReportPeriod::THIS_YEAR]));

        $this->assertCount(now(config('app.display_timezone'))->month, $yearly['labels']);
        $this->assertSame(1000.0, array_sum($yearly['income']));
    }

    public function test_report_period_resolves_presets_and_custom_ranges(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-15 10:00', config('app.display_timezone')));

        $lastMonth = ReportPeriod::fromFilters(['period' => ReportPeriod::LAST_MONTH]);
        $this->assertSame('2026-02-01', $lastMonth->start->toDateString());
        $this->assertSame('2026-02-28', $lastMonth->end->toDateString());

        $thisMonth = ReportPeriod::fromFilters(null);
        $this->assertSame('2026-03-01', $thisMonth->start->toDateString());
        $this->assertSame('2026-03-15', $thisMonth->end->toDateString());
        $this->assertSame('2026-02-28 18:00:00', $thisMonth->startUtc()->toDateTimeString());

        $reversed = ReportPeriod::fromFilters(['period' => ReportPeriod::CUSTOM, 'start_date' => '2026-03-10', 'end_date' => '2026-01-05']);
        $this->assertSame('2026-01-05', $reversed->start->toDateString());
        $this->assertSame('2026-03-10', $reversed->end->toDateString());
        $this->assertTrue($reversed->isMonthly());

        CarbonImmutable::setTestNow();
    }

    public function test_widgets_render_for_every_period(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        $order = Order::factory()->status(OrderStatus::Delivered)->create(['grand_total' => '1000.00', 'delivered_at' => now()]);
        $this->addItem($order, quantity: 1);
        LedgerEntry::factory()->category(LedgerCategory::Salary)->create(['amount' => '5000.00']);

        foreach ([ReportPeriod::TODAY, ReportPeriod::THIS_YEAR, ReportPeriod::CUSTOM] as $period) {
            $filters = ['period' => $period, 'start_date' => now()->subDays(100)->toDateString(), 'end_date' => now()->toDateString()];

            Livewire::test(FinanceOrderStats::class, ['pageFilters' => $filters])->assertOk()->assertSee('Earned');
            Livewire::test(FinanceProfitStats::class, ['pageFilters' => $filters])->assertOk()->assertSee('Net loss');
            Livewire::test(FinanceTrendChart::class, ['pageFilters' => $filters])->assertOk();
            Livewire::test(ExpenseBreakdownChart::class, ['pageFilters' => $filters])->assertOk();
            Livewire::test(ProfitLossStatement::class, ['pageFilters' => $filters])
                ->assertOk()
                ->assertSee('Staff salary')
                ->assertSee('had no cost price');
        }
    }

    private function addItem(Order $order, int $quantity, ?string $unitCost = null, ?int $variantId = null): void
    {
        $order->items()->create([
            'product_variant_id' => $variantId,
            'product_name' => 'Test Item',
            'sku' => 'SKU-'.fake()->unique()->numberBetween(1, 99999),
            'regular_price' => '1000.00',
            'unit_price' => '1000.00',
            'unit_cost' => $unitCost,
            'quantity' => $quantity,
            'line_total' => number_format(1000 * $quantity, 2, '.', ''),
        ]);
    }
}
