<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Pages\ManageSiteSettings;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\ShippingMethods\Pages\EditShippingMethod;
use App\Models\Category;
use App\Models\Page;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Services\SettingsService;
use App\Support\Localization;
use Database\Seeders\ShippingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BanglaContentFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->superAdmin()->create());
    }

    public function test_category_form_saves_the_english_and_bangla_name(): void
    {
        Livewire::test(CreateCategory::class)
            ->assertSee('বাংলা')
            ->fillForm([
                'name' => 'Smart Watch',
                'name_bn' => 'স্মার্ট ওয়াচ',
                'description_bn' => 'সেরা স্মার্ট ওয়াচ',
                'slug' => 'smart-watch',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $category = Category::query()->where('slug', 'smart-watch')->sole();

        $this->assertSame('Smart Watch', $category->name);
        $this->assertSame('স্মার্ট ওয়াচ', $category->name_bn);
        $this->assertSame('স্মার্ট ওয়াচ', $category->translated('name', 'bn'));
        $this->assertSame('Smart Watch', $category->translated('name', 'en'));
    }

    public function test_bangla_is_optional_and_falls_back_to_english(): void
    {
        Livewire::test(CreatePage::class)
            ->fillForm([
                'title' => 'Warranty Policy',
                'slug' => 'warranty-policy',
                'content' => '<p>Official warranty on every product.</p>',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $page = Page::query()->where('slug', 'warranty-policy')->sole();

        $this->assertNull($page->title_bn);
        $this->assertSame('Warranty Policy', $page->translated('title', 'bn'));

        $this->withCookie(Localization::COOKIE, 'bn')
            ->get(route('store.pages.show', $page))
            ->assertOk()
            ->assertSee('Warranty Policy')
            ->assertSee('হোম');
    }

    public function test_shipping_method_bangla_text_reaches_checkout(): void
    {
        $this->seed(ShippingSeeder::class);
        $method = ShippingMethod::query()->where('code', 'inside_dhaka')->sole();

        Livewire::test(EditShippingMethod::class, ['record' => $method->getRouteKey()])
            ->fillForm([
                'name_bn' => 'ঢাকার ভিতরে',
                'delivery_estimate_bn' => '১-২ কর্মদিবস',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $method->refresh();

        $this->assertSame('ঢাকার ভিতরে', $method->translated('name', 'bn'));
        $this->assertSame('1-2 working days', $method->translated('delivery_estimate', 'en'));
    }

    public function test_site_settings_set_the_default_language_and_bangla_notice(): void
    {
        Livewire::test(ManageSiteSettings::class)
            ->assertSee('Default Language')
            ->fillForm([
                'localization.default_locale' => 'bn',
                'localization.show_switcher' => true,
                'announcement.enabled' => true,
                'announcement.text' => 'Free delivery this week',
                'announcement.text_bn' => 'এই সপ্তাহে ফ্রি ডেলিভারি',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = app(SettingsService::class);
        $settings->flush();

        $this->assertSame('bn', $settings->get('localization.default_locale'));

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('এই সপ্তাহে ফ্রি ডেলিভারি')
            ->assertDontSee('Free delivery this week');
    }
}
