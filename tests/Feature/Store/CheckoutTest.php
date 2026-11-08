<?php

namespace Tests\Feature\Store;

use App\Enums\OrderStatus;
use App\Enums\StockStatus;
use App\Models\District;
use App\Models\FlashSaleProduct;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use Database\Seeders\ShippingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ShippingSeeder::class);
    }

    public function test_add_to_cart_goes_to_the_order_page(): void
    {
        $product = Product::factory()->priced('25000.00')->create(['name' => 'Test Galaxy Phone']);

        $this->post(route('store.cart.store'), ['product_id' => $product->id, 'quantity' => 2])
            ->assertRedirect(route('store.checkout.show'));

        $this->get(route('store.checkout.show'))
            ->assertOk()
            ->assertSee('Test Galaxy Phone')
            ->assertSee('৳50,000')
            ->assertSee('2 items')
            ->assertSee('Delivery Information')
            ->assertSee('Place Order');
    }

    public function test_product_that_cannot_be_ordered_is_not_added(): void
    {
        $product = Product::factory()->priced('1000.00', null, 0)->create();
        $this->assertSame(StockStatus::OutOfStock, $product->fresh()->stock_status);

        $this->from(route('store.products.show', $product->slug))
            ->post(route('store.cart.store'), ['product_id' => $product->id])
            ->assertRedirect(route('store.products.show', $product->slug))
            ->assertSessionHasErrors('product_id');

        $this->assertSame([], session(CartService::SESSION_KEY, []));
    }

    public function test_placing_an_order_saves_it_takes_stock_and_shows_thank_you_message(): void
    {
        $product = Product::factory()->priced('25000.00', null, 5)->create(['name' => 'Test Galaxy Phone']);
        $dhaka = District::query()->where('name', 'Dhaka')->firstOrFail();

        $this->post(route('store.cart.store'), ['product_id' => $product->id, 'quantity' => 2]);

        $response = $this->post(route('store.checkout.store'), [
            'customer_name' => 'Rahim Uddin',
            'customer_phone' => '+880 1712-345678',
            'district_id' => $dhaka->id,
            'shipping_area' => 'Mirpur',
            'shipping_address' => 'House 1, Road 2',
            'payment_method' => 'cod',
        ]);

        $order = Order::query()->with('items', 'statusHistories')->sole();

        $response->assertRedirect(route('store.checkout.thank-you', $order));

        $this->assertSame('01712345678', $order->customer_phone);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame('Dhaka', $order->shipping_district);
        $this->assertSame('Inside Dhaka', $order->shipping_zone_name);
        $this->assertSame('50000.00', $order->subtotal);
        $this->assertSame('60.00', $order->shipping_total);
        $this->assertSame('50060.00', $order->grand_total);
        $this->assertSame(2, $order->items->sole()->quantity);
        $this->assertSame('25000.00', $order->items->sole()->unit_price);
        $this->assertCount(1, $order->statusHistories);
        $this->assertSame(3, $product->defaultVariant()->firstOrFail()->stock_quantity);
        $this->assertSame([], session(CartService::SESSION_KEY, []));

        $this->get(route('store.checkout.thank-you', $order))
            ->assertOk()
            ->assertSee('Thank you for your order!')
            ->assertSee($order->order_number)
            ->assertSee('Test Galaxy Phone')
            ->assertSee('৳50,060');
    }

    public function test_thank_you_page_is_hidden_from_other_visitors(): void
    {
        $order = Order::factory()->create();

        $this->get(route('store.checkout.thank-you', $order))->assertNotFound();
    }

    public function test_delivery_details_are_required(): void
    {
        $product = Product::factory()->create();
        $this->post(route('store.cart.store'), ['product_id' => $product->id]);

        $this->post(route('store.checkout.store'), ['customer_phone' => '12345', 'payment_method' => 'cod'])
            ->assertSessionHasErrors(['customer_name', 'customer_phone', 'district_id', 'shipping_address']);

        $this->assertSame(0, Order::query()->count());
    }

    public function test_disabled_payment_method_is_rejected(): void
    {
        $product = Product::factory()->create();
        $this->post(route('store.cart.store'), ['product_id' => $product->id]);

        $this->post(route('store.checkout.store'), $this->deliveryDetails(['payment_method' => 'bkash']))
            ->assertSessionHasErrors('payment_method');
    }

    public function test_empty_cart_cannot_be_ordered(): void
    {
        $this->get(route('store.checkout.show'))
            ->assertOk()
            ->assertSee('Your cart is empty');

        $this->post(route('store.checkout.store'), $this->deliveryDetails())
            ->assertSessionHasErrors('cart');

        $this->assertSame(0, Order::query()->count());
    }

    public function test_order_larger_than_stock_is_rejected(): void
    {
        $product = Product::factory()->priced('1000.00', null, 2)->create(['name' => 'Last Units Phone']);
        $this->post(route('store.cart.store'), ['product_id' => $product->id, 'quantity' => 3]);

        $this->post(route('store.checkout.store'), $this->deliveryDetails())
            ->assertSessionHasErrors(['cart' => 'Only 2 of Last Units Phone left in stock. Please lower the quantity.']);

        $this->assertSame(0, Order::query()->count());
        $this->assertSame(2, $product->defaultVariant()->firstOrFail()->stock_quantity);
    }

    public function test_flash_sale_price_is_charged_and_counted_as_sold(): void
    {
        $product = Product::factory()->priced('10000.00')->create();
        $deal = FlashSaleProduct::factory()->for($product)->create(['discount_value' => '20.00', 'quantity_limit' => 5]);

        $this->post(route('store.cart.store'), ['product_id' => $product->id]);
        $this->post(route('store.checkout.store'), $this->deliveryDetails());

        $item = Order::query()->sole()->items()->sole();

        $this->assertSame('8000.00', $item->unit_price);
        $this->assertSame('2000.00', $item->unit_discount);
        $this->assertSame($deal->id, $item->flash_sale_product_id);
        $this->assertSame(1, $deal->fresh()->sold_quantity);
    }

    public function test_cart_quantity_can_be_changed_and_line_removed(): void
    {
        $product = Product::factory()->priced('1000.00')->create(['name' => 'Cable Organizer']);
        $variantId = $product->defaultVariant()->firstOrFail()->id;

        $this->post(route('store.cart.store'), ['product_id' => $product->id]);

        $this->patch(route('store.cart.update', $variantId), ['quantity' => 3])
            ->assertRedirect(route('store.checkout.show'));
        $this->assertSame([$variantId => 3], session(CartService::SESSION_KEY));

        $this->delete(route('store.cart.destroy', $variantId))
            ->assertRedirect(route('store.checkout.show'));
        $this->assertSame([], session(CartService::SESSION_KEY));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function deliveryDetails(array $overrides = []): array
    {
        return [
            'customer_name' => 'Karim Ahmed',
            'customer_phone' => '01812345678',
            'district_id' => District::query()->where('name', 'Chattogram')->value('id') ?? District::query()->value('id'),
            'shipping_address' => 'Agrabad, Chattogram',
            'payment_method' => 'cod',
            ...$overrides,
        ];
    }
}
