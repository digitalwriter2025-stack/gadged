<?php

namespace Tests\Feature\Store;

use App\Models\District;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Services\MetaPixel;
use App\Services\SettingsService;
use Database\Seeders\ShippingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetaPixelTest extends TestCase
{
    use RefreshDatabase;

    private const PIXEL_ID = '1234567890123456';

    public function test_pixel_is_not_loaded_without_a_pixel_id(): void
    {
        $this->get(route('store.home'))
            ->assertOk()
            ->assertDontSee('fbevents.js', false)
            ->assertDontSee('facebook-domain-verification', false);
    }

    public function test_browser_pixel_tracks_the_page_view_with_an_event_id(): void
    {
        $this->configure(['domain_verification' => '<meta name="facebook-domain-verification" content="abc123xyz" />']);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('connect.facebook.net/en_US/fbevents.js', false)
            ->assertSee('fbq(\'init\', "'.self::PIXEL_ID.'", {});', false)
            ->assertSee("fbq('track', 'PageView', {}, {eventID: \"", false)
            ->assertSee('facebook.com/tr?id='.self::PIXEL_ID.'&ev=PageView', false)
            ->assertSee('<meta name="facebook-domain-verification" content="abc123xyz">', false);
    }

    public function test_pixel_id_saved_before_the_meta_section_existed_still_works(): void
    {
        app(SettingsService::class)->set('analytics.meta_pixel_id', self::PIXEL_ID);

        $this->get(route('store.home'))->assertSee('fbq(\'init\', "'.self::PIXEL_ID.'"', false);
    }

    public function test_browser_pixel_can_be_turned_off_when_tag_manager_loads_it(): void
    {
        $this->configure(['browser_enabled' => false]);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertDontSee('fbevents.js', false);
    }

    public function test_product_page_tracks_view_content(): void
    {
        $this->configure();
        $product = Product::factory()->priced('25000.00', '23500.00')->create(['name' => 'Test Galaxy Phone']);

        $this->get(route('store.products.show', $product->slug))
            ->assertOk()
            ->assertSee('"name":"ViewContent"', false)
            ->assertSee('"content_ids":["'.$product->id.'"]', false)
            ->assertSee('"value":23500', false)
            ->assertSee('"currency":"BDT"', false);
    }

    public function test_catalogue_id_can_be_the_product_sku(): void
    {
        $this->configure(['content_id' => 'sku']);
        $product = Product::factory()->priced('25000.00')->create(['sku' => 'GAL-S25']);

        $this->get(route('store.products.show', $product->slug))
            ->assertSee('"content_ids":["GAL-S25"]', false);
    }

    public function test_add_to_cart_and_initiate_checkout_fire_once_on_the_order_page(): void
    {
        $this->configure();
        $product = Product::factory()->priced('25000.00')->create();

        $this->post(route('store.cart.store'), ['product_id' => $product->id, 'quantity' => 2])
            ->assertRedirect(route('store.checkout.show'));

        $this->get(route('store.checkout.show'))
            ->assertOk()
            ->assertSeeInOrder(['"name":"AddToCart"', '"value":50000', '"name":"InitiateCheckout"', '"num_items":2'], false);

        $this->get(route('store.checkout.show'))
            ->assertOk()
            ->assertDontSee('"name":"AddToCart"', false)
            ->assertDontSee('"name":"InitiateCheckout"', false);
    }

    public function test_search_is_tracked(): void
    {
        $this->configure();

        $this->get(route('store.search', ['q' => 'RTX 5070']))
            ->assertOk()
            ->assertSee('"name":"Search","data":{"search_string":"RTX 5070"}', false);
    }

    public function test_purchase_is_sent_to_the_conversions_api_and_the_browser_with_one_event_id(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1, 'fbtrace_id' => 'trace'])]);
        $this->withoutDefer();
        $this->seed(ShippingSeeder::class);
        $this->configure([
            'server_enabled' => true,
            'access_token' => MetaPixel::encryptAccessToken('EAAG-secret-token'),
            'test_event_code' => 'TEST12345',
            'advanced_matching' => true,
        ]);

        $product = Product::factory()->priced('25000.00', null, 5)->create();
        $dhaka = District::query()->where('name', 'Dhaka')->firstOrFail();

        $this->post(route('store.cart.store'), ['product_id' => $product->id]);
        $this->withUnencryptedCookie('_fbp', 'fb.1.1700000000000.123456789')
            ->post(route('store.checkout.store'), [
                'customer_name' => 'Rahim Uddin',
                'customer_phone' => '+880 1712-345678',
                'customer_email' => 'Rahim@Example.com',
                'district_id' => $dhaka->id,
                'shipping_method_id' => ShippingMethod::query()->where('code', 'inside_dhaka')->value('id'),
                'shipping_address' => 'House 1, Road 2',
                'payment_method' => 'cod',
            ]);

        $order = Order::query()->sole();
        $eventId = 'purchase-'.$order->order_number;

        Http::assertSent(function (HttpRequest $request) use ($order, $eventId): bool {
            $event = $request['data'][0];

            return $request->url() === 'https://graph.facebook.com/v21.0/'.self::PIXEL_ID.'/events'
                && $request['access_token'] === 'EAAG-secret-token'
                && $request['test_event_code'] === 'TEST12345'
                && $event['event_name'] === 'Purchase'
                && $event['event_id'] === $eventId
                && $event['action_source'] === 'website'
                && $event['custom_data']['value'] === (float) $order->grand_total
                && $event['custom_data']['order_id'] === $order->order_number
                && $event['user_data']['fbp'] === 'fb.1.1700000000000.123456789'
                && $event['user_data']['ph'] === hash('sha256', '8801712345678')
                && $event['user_data']['em'] === hash('sha256', 'rahim@example.com')
                && $event['user_data']['fn'] === hash('sha256', 'rahim')
                && $event['user_data']['ln'] === hash('sha256', 'uddin')
                && $event['user_data']['ct'] === hash('sha256', 'dhaka')
                && ! str_contains(json_encode($event), '01712345678');
        });

        $this->get(route('store.checkout.thank-you', $order))
            ->assertOk()
            ->assertSee('"name":"Purchase"', false)
            ->assertSee('"id":"'.$eventId.'"', false)
            ->assertSee('"ph":"'.hash('sha256', '8801712345678').'"', false);
    }

    public function test_server_events_are_not_sent_without_an_access_token(): void
    {
        Http::fake();
        $this->withoutDefer();
        $this->configure(['server_enabled' => true]);

        $this->get(route('store.home'))->assertOk();

        Http::assertNothingSent();
    }

    public function test_a_conversions_api_error_does_not_break_the_order(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['message' => 'Invalid OAuth access token.']], 400)]);
        $this->withoutDefer();
        $this->seed(ShippingSeeder::class);
        $this->configure([
            'server_enabled' => true,
            'access_token' => MetaPixel::encryptAccessToken('expired-token'),
        ]);

        $product = Product::factory()->priced('25000.00', null, 5)->create();

        $this->post(route('store.cart.store'), ['product_id' => $product->id]);
        $this->post(route('store.checkout.store'), [
            'customer_name' => 'Rahim Uddin',
            'customer_phone' => '01712345678',
            'district_id' => District::query()->where('name', 'Dhaka')->value('id'),
            'shipping_method_id' => ShippingMethod::query()->where('code', 'inside_dhaka')->value('id'),
            'shipping_address' => 'House 1, Road 2',
            'payment_method' => 'cod',
        ])->assertRedirect(route('store.checkout.thank-you', Order::query()->sole()));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function configure(array $overrides = []): void
    {
        app(SettingsService::class)->setMany('meta_pixel', ['pixel_id' => self::PIXEL_ID, ...$overrides]);
    }
}
