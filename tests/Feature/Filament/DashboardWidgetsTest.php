<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\OrderStatus;
use App\Enums\StaffRole;
use App\Enums\StockStatus;
use App\Filament\Widgets\OrdersByStatusChart;
use App\Filament\Widgets\ProductsByCategoryChart;
use App\Filament\Widgets\RevenueChart;
use App\Filament\Widgets\StockStatusChart;
use App\Filament\Widgets\StoreStatsOverview;
use App\Filament\Widgets\TopSellingProductsChart;
use App\Filament\Widgets\WelcomeBanner;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\SalesReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_every_widget_for_super_admin(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->get('/admin')
            ->assertSuccessful()
            ->assertSeeLivewire(WelcomeBanner::class)
            ->assertSeeLivewire(StoreStatsOverview::class)
            ->assertSeeLivewire(RevenueChart::class)
            ->assertSeeLivewire(OrdersByStatusChart::class)
            ->assertSeeLivewire(StockStatusChart::class)
            ->assertSeeLivewire(ProductsByCategoryChart::class)
            ->assertSeeLivewire(TopSellingProductsChart::class);
    }

    public function test_sales_widgets_are_hidden_from_staff_without_order_access(): void
    {
        $this->actingAs(User::factory()->staff(StaffRole::ContentManager)->create())
            ->get('/admin')
            ->assertSuccessful()
            ->assertDontSeeLivewire(RevenueChart::class)
            ->assertDontSeeLivewire(OrdersByStatusChart::class)
            ->assertSeeLivewire(StockStatusChart::class);
    }

    public function test_revenue_trend_counts_only_revenue_orders_in_their_local_day(): void
    {
        Order::factory()->status(OrderStatus::Delivered)->create(['grand_total' => '1500.00', 'created_at' => now()]);
        Order::factory()->status(OrderStatus::Confirmed)->create(['grand_total' => '500.00', 'created_at' => now()]);
        Order::factory()->status(OrderStatus::Cancelled)->create(['grand_total' => '9000.00', 'created_at' => now()]);
        Order::factory()->status(OrderStatus::Delivered)->create(['grand_total' => '700.00', 'created_at' => now()->subDays(45)]);

        $trend = app(SalesReportService::class)->trend(SalesReportService::PERIOD_MONTH);

        $this->assertCount(30, $trend['labels']);
        $this->assertSame(2000.0, end($trend['revenue']));
        $this->assertSame(2, end($trend['orders']));
        $this->assertSame(2000.0, array_sum($trend['revenue']));

        $this->assertCount(12, app(SalesReportService::class)->trend(SalesReportService::PERIOD_YEAR)['labels']);
        $this->assertCount(7, app(SalesReportService::class)->trend(SalesReportService::PERIOD_WEEK)['labels']);
    }

    public function test_summary_compares_with_the_previous_period(): void
    {
        Order::factory()->status(OrderStatus::Delivered)->create(['grand_total' => '1500.00', 'created_at' => now()->subDays(2)]);
        Order::factory()->status(OrderStatus::Delivered)->create(['grand_total' => '1000.00', 'created_at' => now()->subDays(40)]);

        $summary = app(SalesReportService::class)->summary(30);

        $this->assertSame(1500.0, $summary['revenue']);
        $this->assertSame(1, $summary['orders']);
        $this->assertSame(1000.0, $summary['previous_revenue']);
        $this->assertSame(50.0, SalesReportService::percentChange($summary['revenue'], $summary['previous_revenue']));
        $this->assertNull(SalesReportService::percentChange(100, 0));
    }

    public function test_chart_widgets_render_with_data(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        Product::factory()->create(['sales_count' => 12]);
        Product::factory()->create(['stock_status' => StockStatus::OutOfStock]);
        Order::factory()->status(OrderStatus::Delivered)->create();

        Livewire::test(RevenueChart::class)->assertOk()->set('filter', SalesReportService::PERIOD_YEAR)->assertOk();
        Livewire::test(OrdersByStatusChart::class)->assertOk();
        Livewire::test(StockStatusChart::class)->assertOk();
        Livewire::test(ProductsByCategoryChart::class)->assertOk();
        Livewire::test(TopSellingProductsChart::class)->assertOk();
        Livewire::test(StoreStatsOverview::class)->assertOk()->assertSee('Out of stock');
    }

    public function test_welcome_banner_only_offers_shortcuts_the_staff_member_can_use(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create(['name' => 'Rahim']));

        Livewire::test(WelcomeBanner::class)
            ->assertOk()
            ->assertSee('Rahim')
            ->assertSee('Add product')
            ->assertSee('Visit store');

        $this->actingAs(User::factory()->staff(StaffRole::OrderManager)->create());

        Livewire::test(WelcomeBanner::class)
            ->assertOk()
            ->assertDontSee('Add product')
            ->assertSee('Products');
    }
}
