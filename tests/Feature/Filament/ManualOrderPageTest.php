<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\OrderSource;
use App\Enums\PaymentMethod;
use App\Enums\StaffRole;
use App\Filament\Pages\ManualOrder;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManualOrderPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_the_page(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        $this->get(ManualOrder::getUrl())
            ->assertOk()
            ->assertSee('Customer Information')
            ->assertSee('Add Products')
            ->assertSee('No products added yet');
    }

    public function test_staff_without_order_create_permission_cannot_open_it(): void
    {
        $this->actingAs(User::factory()->staff(StaffRole::ContentManager)->create());

        $this->get(ManualOrder::getUrl())->assertForbidden();
    }

    public function test_search_lists_matching_products(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        Product::factory()->priced('1500.00')->create(['name' => 'Wireless Mouse']);
        Product::factory()->priced('900.00')->create(['name' => 'USB Cable']);

        Livewire::test(ManualOrder::class)
            ->set('search', 'mouse')
            ->assertSee('Wireless Mouse')
            ->assertDontSee('USB Cable');
    }

    public function test_manual_order_is_placed_with_discount_and_delivery_charge(): void
    {
        $staff = User::factory()->superAdmin()->create();
        $this->actingAs($staff);
        $product = Product::factory()->priced('1000.00', null, 5)->create(['name' => 'Smart Watch']);
        $variant = $product->defaultVariant()->firstOrFail();
        $method = ShippingMethod::query()->create(['name' => 'Inside Dhaka', 'charge' => '60']);

        Livewire::test(ManualOrder::class)
            ->set('customerName', 'Rahim')
            ->set('customerPhone', '+880 1712-345678')
            ->set('shippingAddress', 'House 1, Road 2, Dhaka')
            ->set('shippingMethodId', (string) $method->id)
            ->set('paymentMethod', PaymentMethod::Bkash->value)
            ->call('addProduct', $variant->id)
            ->call('changeQuantity', 0, 1)
            ->set('extraDiscount', '100')
            ->assertSee('Smart Watch')
            ->call('placeOrder')
            ->assertHasNoErrors()
            ->assertSet('items', []);

        $order = Order::query()->with('items')->sole();

        $this->assertSame(OrderSource::Admin, $order->source);
        $this->assertSame('01712345678', $order->customer_phone);
        $this->assertSame($staff->id, $order->created_by);
        $this->assertSame('2000.00', $order->subtotal);
        $this->assertSame('100.00', $order->manual_discount);
        $this->assertSame('60.00', $order->shipping_total);
        $this->assertSame('1960.00', $order->grand_total);
        $this->assertSame(2, $order->items->sole()->quantity);
        $this->assertSame(3, $variant->fresh()->stock_quantity);
    }

    public function test_order_needs_customer_details_and_a_product(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        ShippingMethod::query()->create(['name' => 'Inside Dhaka', 'charge' => '60']);

        Livewire::test(ManualOrder::class)
            ->set('customerPhone', '12345')
            ->call('placeOrder')
            ->assertHasErrors(['customerName', 'customerPhone', 'shippingAddress', 'shippingMethodId', 'items']);

        $this->assertSame(0, Order::query()->count());
    }

    public function test_discount_cannot_exceed_the_product_total(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $variant = Product::factory()->priced('500.00')->create()->defaultVariant()->firstOrFail();

        Livewire::test(ManualOrder::class)
            ->set('customerName', 'Karim')
            ->set('customerPhone', '01812345678')
            ->set('shippingAddress', 'Chattogram')
            ->call('addProduct', $variant->id)
            ->set('extraDiscount', '600')
            ->call('placeOrder')
            ->assertNotified('Order could not be created');

        $this->assertSame(0, Order::query()->count());
    }
}
