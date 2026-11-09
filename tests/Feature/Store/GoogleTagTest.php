<?php

namespace Tests\Feature\Store;

use App\Models\District;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Services\SettingsService;
use Database\Seeders\ShippingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleTagTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_google_tag_is_loaded_without_an_id(): void
    {
        $this->get(route('store.home'))
            ->assertOk()
            ->assertDontSee('googletagmanager.com', false)
            ->assertDontSee('storeAnalytics', false);
    }

    public function test_tag_manager_prints_the_head_and_body_snippets(): void
    {
        $this->configure(['gtm_id' => 'GTM-ABC1234']);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee("'script','dataLayer',\"GTM-ABC1234\")", false)
            ->assertSee('https://www.googletagmanager.com/ns.html?id=GTM-ABC1234', false)
            ->assertDontSee('gtag/js', false);
    }

    public function test_ga4_and_google_ads_are_loaded_through_gtag(): void
    {
        $this->configure(['ga4_measurement_id' => 'G-TEST12345', 'google_ads_id' => 'AW-123456789']);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('googletagmanager.com/gtag/js?id=G-TEST12345', false)
            ->assertSee('gtag(\'config\', "G-TEST12345");', false)
            ->assertSee('gtag(\'config\', "AW-123456789");', false)
            ->assertDontSee('gtm.js', false);
    }

    public function test_the_old_single_google_tag_field_still_works(): void
    {
        $this->configure(['google_tag_id' => 'gtm-old1234']);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('ns.html?id=GTM-OLD1234', false);
    }

    public function test_an_id_of_the_wrong_kind_is_ignored(): void
    {
        $this->configure(['gtm_id' => 'G-TEST12345']);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertDontSee('googletagmanager.com', false);
    }

    public function test_product_page_pushes_view_item_once(): void
    {
        $this->configure(['gtm_id' => 'GTM-ABC1234']);
        $product = Product::factory()->priced('25000.00', '23500.00')->create(['name' => 'Test Galaxy Phone']);

        $this->get(route('store.products.show', $product->slug))
            ->assertOk()
            ->assertSee('"name":"view_item"', false)
            ->assertSee('"currency":"BDT","value":23500,"items":[{"item_id":"'.$product->id.'","item_name":"Test Galaxy Phone"', false);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertDontSee('"name":"view_item"', false);
    }

    public function test_add_to_cart_and_begin_checkout_are_pushed_once_on_the_order_page(): void
    {
        $this->configure(['gtm_id' => 'GTM-ABC1234']);
        $product = Product::factory()->priced('25000.00')->create();

        $this->post(route('store.cart.store'), ['product_id' => $product->id, 'quantity' => 2])
            ->assertRedirect(route('store.checkout.show'));

        $this->get(route('store.checkout.show'))
            ->assertOk()
            ->assertSeeInOrder(['"name":"add_to_cart"', '"value":50000', '"quantity":2', '"name":"begin_checkout"'], false);

        $this->get(route('store.checkout.show'))
            ->assertOk()
            ->assertDontSee('"name":"add_to_cart"', false)
            ->assertDontSee('"name":"begin_checkout"', false);
    }

    public function test_search_is_pushed(): void
    {
        $this->configure(['ga4_measurement_id' => 'G-TEST12345']);

        $this->get(route('store.search', ['q' => 'RTX 5070']))
            ->assertOk()
            ->assertSee('"name":"search"', false)
            ->assertSee('"params":{"search_term":"RTX 5070"}', false);
    }

    public function test_purchase_is_pushed_once_with_the_order_number_and_hashed_customer(): void
    {
        $this->seed(ShippingSeeder::class);
        $this->configure([
            'gtm_id' => 'GTM-ABC1234',
            'google_ads_id' => 'AW-123456789',
            'google_ads_conversion_label' => 'AbC-D_efG-h12',
        ]);

        $product = Product::factory()->priced('25000.00', null, 5)->create();

        $this->post(route('store.cart.store'), ['product_id' => $product->id]);
        $this->post(route('store.checkout.store'), [
            'customer_name' => 'Rahim Uddin',
            'customer_phone' => '01712-345678',
            'customer_email' => 'Rahim.Uddin@Gmail.com',
            'district_id' => District::query()->where('name', 'Dhaka')->value('id'),
            'shipping_method_id' => ShippingMethod::query()->where('code', 'inside_dhaka')->value('id'),
            'shipping_address' => 'House 1, Road 2',
            'payment_method' => 'cod',
        ]);

        $order = Order::query()->sole();

        $this->get(route('store.checkout.thank-you', $order))
            ->assertOk()
            ->assertSee('"name":"purchase","id":"purchase-'.$order->order_number.'"', false)
            ->assertSee('"transaction_id":"'.$order->order_number.'"', false)
            ->assertSee('"value":'.(float) $order->grand_total, false)
            ->assertSee('"adsConversion":"AW-123456789\/AbC-D_efG-h12"', false)
            ->assertSee('"user_data":{"sha256_email_address":"'.hash('sha256', 'rahimuddin@gmail.com').'","sha256_phone_number":"'.hash('sha256', '+8801712345678').'"}', false);

        $this->get(route('store.checkout.thank-you', $order))
            ->assertOk()
            ->assertDontSee('"name":"purchase"', false);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function configure(array $values): void
    {
        app(SettingsService::class)->setMany('analytics', $values);
    }
}
