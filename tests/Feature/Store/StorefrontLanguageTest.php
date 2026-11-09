<?php

namespace Tests\Feature\Store;

use App\Models\Category;
use App\Models\Product;
use App\Services\SettingsService;
use App\Support\Localization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontLanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_is_english_by_default_with_both_language_buttons(): void
    {
        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Register or Login')
            ->assertSee('data-language-switcher', false)
            ->assertSee('EN')
            ->assertSee('বাং');
    }

    public function test_first_visit_uses_the_default_language_set_in_the_admin(): void
    {
        app(SettingsService::class)->set('localization.default_locale', 'bn');

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('<html lang="bn">', false)
            ->assertSee('রেজিস্টার বা লগইন')
            ->assertDontSee('Register or Login');
    }

    public function test_choosing_a_language_saves_it_and_returns_to_the_same_page(): void
    {
        $product = Product::factory()->create();
        $productUrl = route('store.products.show', $product->slug);

        $this->from($productUrl)
            ->post(route('store.locale.update'), ['locale' => 'bn'])
            ->assertRedirect($productUrl)
            ->assertCookie(Localization::COOKIE, 'bn')
            ->assertHeader('Clear-Site-Data', '"cache"');
    }

    public function test_an_unknown_language_is_rejected(): void
    {
        $this->from(route('store.home'))
            ->post(route('store.locale.update'), ['locale' => 'fr'])
            ->assertSessionHasErrors('locale')
            ->assertCookieMissing(Localization::COOKIE);
    }

    public function test_the_visitors_choice_wins_over_the_admin_default(): void
    {
        app(SettingsService::class)->set('localization.default_locale', 'bn');

        $this->withCookie(Localization::COOKIE, 'en')
            ->get(route('store.home'))
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Register or Login');
    }

    public function test_bangla_shows_admin_translations_and_falls_back_to_english(): void
    {
        $category = Category::factory()->create(['name' => 'Smart Watch', 'name_bn' => 'স্মার্ট ওয়াচ']);
        Product::factory()->create(['category_id' => $category->id, 'name' => 'Haylou Solar Pro Watch', 'name_bn' => 'হেইলু সোলার প্রো ওয়াচ']);
        Product::factory()->create(['category_id' => $category->id, 'name' => 'Kieslect KS3 Watch', 'name_bn' => null]);

        $this->withCookie(Localization::COOKIE, 'bn')
            ->get(route('store.categories.show', $category->slug))
            ->assertOk()
            ->assertSee('বাংলাদেশে স্মার্ট ওয়াচ-এর দাম')
            ->assertSee('হেইলু সোলার প্রো ওয়াচ')
            ->assertDontSee('Haylou Solar Pro Watch')
            ->assertSee('Kieslect KS3 Watch');
    }

    public function test_bangla_prices_use_bangla_digits(): void
    {
        $product = Product::factory()->priced('12500')->create();

        $this->withCookie(Localization::COOKIE, 'bn')
            ->get(route('store.products.show', $product->slug))
            ->assertOk()
            ->assertSee('৳১২,৫০০')
            ->assertSee('কার্টে যোগ করুন');

        $this->withCookie(Localization::COOKIE, 'en')
            ->get(route('store.products.show', $product->slug))
            ->assertOk()
            ->assertSee('৳12,500')
            ->assertSee('Add to Cart');
    }

    public function test_menus_are_cached_separately_for_each_language(): void
    {
        Category::factory()->create(['name' => 'Powerbanks', 'name_bn' => 'পাওয়ার ব্যাংক']);

        $this->withCookie(Localization::COOKIE, 'en')->get(route('store.home'))->assertSee('Powerbanks');

        $this->withCookie(Localization::COOKIE, 'bn')
            ->get(route('store.home'))
            ->assertSee('পাওয়ার ব্যাংক')
            ->assertDontSee('>Powerbanks<', false);
    }

    public function test_switch_can_be_hidden_from_the_admin(): void
    {
        app(SettingsService::class)->set('localization.show_switcher', false);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertDontSee('data-language-switcher', false);
    }

    public function test_bangla_validation_messages_on_checkout_forms(): void
    {
        $this->withCookie(Localization::COOKIE, 'bn')
            ->from(route('store.orders.track'))
            ->post(route('store.orders.lookup'), ['order_number' => '', 'phone' => ''])
            ->assertSessionHasErrors(['order_number' => 'অর্ডার নম্বর দেওয়া আবশ্যক।']);
    }

    public function test_admin_panel_stays_english_for_a_bangla_visitor(): void
    {
        $this->withCookie(Localization::COOKIE, 'bn')
            ->get('/admin/login')
            ->assertOk()
            ->assertSee('lang="en"', false)
            ->assertDontSee('lang="bn"', false);
    }
}
