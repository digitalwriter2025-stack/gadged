<?php

namespace Tests\Feature\Store;

use App\Enums\StockStatus;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_lists_matching_published_products(): void
    {
        Product::factory()->create(['name' => 'Gigabyte RTX 5070 Ti Gaming OC']);
        Product::factory()->create(['name' => 'Walton Refrigerator', 'model' => 'RTX-FRIDGE']);
        Product::factory()->draft()->create(['name' => 'Draft RTX Card']);
        Product::factory()->create(['name' => 'Logitech Mouse']);

        $this->get(route('store.search', ['q' => 'RTX']))
            ->assertOk()
            ->assertSee('Search results for “RTX”')
            ->assertSee('2 products found')
            ->assertSee('Gigabyte RTX 5070 Ti Gaming OC')
            ->assertSee('Walton Refrigerator')
            ->assertDontSee('Draft RTX Card')
            ->assertDontSee('Logitech Mouse')
            ->assertSee('<meta name="robots" content="noindex,follow">', false)
            ->assertSee('value="RTX"', false);
    }

    public function test_search_results_can_be_filtered_by_brand_and_stock(): void
    {
        $gigabyte = Brand::factory()->create(['name' => 'Gigabyte']);
        $asus = Brand::factory()->create(['name' => 'Asus']);

        Product::factory()->create(['name' => 'Gigabyte RTX Card', 'brand_id' => $gigabyte->id]);
        Product::factory()->create(['name' => 'Asus RTX Card', 'brand_id' => $asus->id]);

        $this->get(route('store.search', ['q' => 'RTX', 'brand' => [$gigabyte->slug]]))
            ->assertOk()
            ->assertSee('Gigabyte RTX Card')
            ->assertDontSee('Asus RTX Card');

        $this->get(route('store.search', ['q' => 'RTX', 'stock' => [StockStatus::OutOfStock->value]]))
            ->assertOk()
            ->assertSee('No products found');
    }

    public function test_search_page_shows_empty_state_when_nothing_matches(): void
    {
        $this->get(route('store.search', ['q' => 'nothing-here']))
            ->assertOk()
            ->assertSee('No products found');
    }

    public function test_blank_search_redirects_home(): void
    {
        $this->get(route('store.search', ['q' => '  ']))
            ->assertRedirect(route('store.home'));
    }
}
