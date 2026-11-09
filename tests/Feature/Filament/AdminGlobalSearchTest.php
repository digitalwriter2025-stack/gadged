<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\StaffRole;
use App\Filament\Pages\ManageSiteSettings;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\GlobalSearch\GlobalSearchResults;
use Filament\Livewire\GlobalSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminGlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');
    }

    public function test_searching_setting_lists_the_settings_pages_and_the_site_settings_tabs(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        $results = $this->search('setting');

        $this->assertEqualsCanonicalizing(
            ['Site Settings', 'Shipping Methods', 'Steadfast Courier', 'Cache Manager'],
            $this->titles($results, 'Admin Menu'),
        );
        $this->assertContains('General', $this->titles($results, 'Settings'));
        $this->assertContains('SEO & Analytics', $this->titles($results, 'Settings'));
    }

    public function test_a_settings_field_links_to_the_tab_that_holds_it(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        $result = $this->findResult($this->search('whatsapp number'), 'Settings', 'WhatsApp Number (with country code)');

        $this->assertSame(['Site Settings › Contact & Location'], $result->details);
        $this->assertSame(ManageSiteSettings::getUrl(['tab' => 'contact-location::data::tab']), $result->url);
    }

    public function test_the_topbar_search_shows_settings_results(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test(GlobalSearch::class)
            ->set('search', 'favicon')
            ->assertSee('Favicon')
            ->assertSee('Site Settings › General');
    }

    public function test_products_are_found_by_name_sku_or_bangla_name(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        $product = Product::factory()->create([
            'name' => 'Anker PowerCore 10000',
            'name_bn' => 'অ্যাঙ্কর পাওয়ারব্যাংক',
            'sku' => 'ANK-PC10K',
        ]);

        foreach (['powercore', 'ANK-PC10K', 'পাওয়ারব্যাংক'] as $query) {
            $result = $this->findResult($this->search($query), 'Products', 'Anker PowerCore 10000');

            $this->assertSame(ProductResource::getUrl('edit', ['record' => $product]), $result->url, "Searching [{$query}]");
        }
    }

    public function test_staff_only_find_the_admin_pages_they_may_open(): void
    {
        $this->actingAs(User::factory()->staff(StaffRole::ProductManager)->create());

        $this->assertContains('Products', $this->titles($this->search('product'), 'Admin Menu'));
        $this->assertNotContains('Shipping Methods', $this->titles($this->search('shipping'), 'Admin Menu'));
    }

    private function search(string $query): GlobalSearchResults
    {
        return Filament::getGlobalSearchProvider()->getResults($query);
    }

    /**
     * @return list<string>
     */
    private function titles(GlobalSearchResults $results, string $category): array
    {
        return collect($results->getCategories()->get($category, []))
            ->map(fn (GlobalSearchResult $result): string => (string) $result->title)
            ->values()
            ->all();
    }

    private function findResult(GlobalSearchResults $results, string $category, string $title): GlobalSearchResult
    {
        $result = collect($results->getCategories()->get($category, []))
            ->first(fn (GlobalSearchResult $result): bool => (string) $result->title === $title);

        $this->assertNotNull($result, "No [{$title}] result under [{$category}].");

        return $result;
    }
}
