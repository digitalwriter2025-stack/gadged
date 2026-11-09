<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\OrderStatus;
use App\Enums\StaffRole;
use App\Filament\Resources\Orders\Actions\UpdateOrderStatusAction;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\OrderStatusService;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Livewire\Livewire;
use Tests\TestCase;

class OrderResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_website_orders_in_the_list(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $orders = Order::factory()->count(3)->create();

        Livewire::test(ListOrders::class)
            ->assertOk()
            ->assertCanSeeTableRecords($orders)
            ->assertSee($orders->first()->order_number)
            ->assertSee($orders->first()->customer_name);
    }

    public function test_list_shows_the_ordered_product_images(): void
    {
        Storage::fake('public');
        Storage::disk('public')->putFileAs('14', UploadedFile::fake()->image('test-phone.webp'), 'test-phone.webp');
        $this->actingAs(User::factory()->superAdmin()->create());
        $order = $this->orderWithItem();
        $order->items()->update(['image_path' => '14/test-phone.webp']);

        Livewire::test(ListOrders::class)
            ->assertTableColumnStateSet('image', ['14/test-phone.webp'], $order)
            ->assertSee(Storage::disk('public')->url('14/test-phone.webp'));
    }

    public function test_status_tabs_filter_the_list(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $pending = Order::factory()->create();
        $shipped = Order::factory()->status(OrderStatus::Shipped)->create();

        Livewire::test(ListOrders::class)
            ->set('activeTab', OrderStatus::Shipped->value)
            ->assertCanSeeTableRecords([$shipped])
            ->assertCanNotSeeTableRecords([$pending]);
    }

    public function test_pending_orders_are_counted_in_the_navigation_badge(): void
    {
        Order::factory()->count(2)->create();
        Order::factory()->status(OrderStatus::Delivered)->create();

        $this->assertSame('2', OrderResource::getNavigationBadge());
    }

    public function test_admin_can_open_an_order(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $order = $this->orderWithItem();

        $this->get(OrderResource::getUrl('view', ['record' => $order]))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Test Phone')
            ->assertSee($order->customer_phone);
    }

    public function test_admin_can_confirm_an_order_and_it_is_logged(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $this->actingAs($admin);
        $order = $this->orderWithItem();

        Livewire::test(ViewOrder::class, ['record' => $order->order_number])
            ->callAction(UpdateOrderStatusAction::getDefaultName(), ['status' => OrderStatus::Confirmed->value, 'note' => 'Called the customer'])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $order->refresh();
        $history = $order->statusHistories()->sole();

        $this->assertSame(OrderStatus::Confirmed, $order->status);
        $this->assertNotNull($order->confirmed_at);
        $this->assertSame('pending', $history->from_status);
        $this->assertSame('confirmed', $history->to_status);
        $this->assertSame('Called the customer', $history->note);
        $this->assertSame($admin->id, $history->user_id);
    }

    public function test_status_can_be_changed_from_the_list(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $order = Order::factory()->status(OrderStatus::Packed)->create();

        Livewire::test(ListOrders::class)
            ->callAction(TestAction::make('updateStatus')->table($order), ['status' => OrderStatus::Shipped->value])
            ->assertHasNoFormErrors();

        $this->assertSame(OrderStatus::Shipped, $order->refresh()->status);
        $this->assertNotNull($order->shipped_at);
    }

    public function test_cancelling_an_order_puts_stock_back(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $order = $this->orderWithItem(stock: 5, quantity: 2);
        $variant = $order->items->sole()->variant;

        $this->assertSame(3, $variant->fresh()->stock_quantity);

        app(OrderStatusService::class)->transition($order, OrderStatus::Cancelled, 'Customer changed mind', $admin);

        $this->assertSame(OrderStatus::Cancelled, $order->refresh()->status);
        $this->assertNotNull($order->cancelled_at);
        $this->assertSame(5, $variant->fresh()->stock_quantity);
    }

    public function test_a_move_outside_the_lifecycle_is_refused(): void
    {
        $order = Order::factory()->status(OrderStatus::Delivered)->create();

        $this->expectException(InvalidArgumentException::class);

        app(OrderStatusService::class)->transition($order, OrderStatus::Pending);
    }

    public function test_completed_orders_have_no_status_action(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $order = Order::factory()->status(OrderStatus::Cancelled)->create();

        Livewire::test(ViewOrder::class, ['record' => $order->order_number])
            ->assertActionHidden(UpdateOrderStatusAction::getDefaultName());
    }

    public function test_courier_and_tracking_can_be_saved(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $order = Order::factory()->status(OrderStatus::Packed)->create();

        Livewire::test(ViewOrder::class, ['record' => $order->order_number])
            ->callAction('tracking', ['courier_name' => 'Steadfast', 'tracking_number' => 'SF-123456'])
            ->assertHasNoFormErrors();

        $order->refresh();

        $this->assertSame('Steadfast', $order->courier_name);
        $this->assertSame('SF-123456', $order->tracking_number);
    }

    public function test_staff_without_order_permission_cannot_open_orders(): void
    {
        $this->actingAs(User::factory()->staff(StaffRole::ContentManager)->create())
            ->get(OrderResource::getUrl())
            ->assertForbidden();
    }

    private function orderWithItem(int $stock = 10, int $quantity = 1): Order
    {
        $product = Product::factory()->priced('1000.00', null, $stock)->create(['name' => 'Test Phone']);
        $variant = $product->defaultVariant()->firstOrFail();
        $order = Order::factory()->create();

        $order->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'sku' => $variant->sku,
            'regular_price' => '1000.00',
            'unit_price' => '1000.00',
            'quantity' => $quantity,
            'line_total' => number_format(1000 * $quantity, 2, '.', ''),
        ]);

        app(InventoryService::class)->deductForOrder([$variant->id => $quantity], $order);

        return $order->load('items.variant');
    }
}
