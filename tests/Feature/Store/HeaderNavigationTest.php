<?php

namespace Tests\Feature\Store;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeaderNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_invites_guests_to_register_or_login(): void
    {
        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('Register or Login');
    }

    public function test_header_renders_menu_categories_with_mega_menu_children(): void
    {
        $laptop = Category::factory()->create(['name' => 'Laptop Menu', 'sort_order' => 1]);
        $gaming = Category::factory()->childOf($laptop)->create(['name' => 'Gaming Laptop']);
        Category::factory()->childOf($gaming)->create(['name' => 'RTX Laptop']);
        Category::factory()->childOf($laptop)->inactive()->create(['name' => 'Inactive Laptop']);
        Category::factory()->create(['name' => 'Hidden Root', 'show_in_menu' => false]);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('data-mega-panel', false)
            ->assertSeeInOrder(['Laptop Menu', 'Gaming Laptop', 'RTX Laptop'])
            ->assertSee(route('store.categories.show', $gaming->slug))
            ->assertDontSee('Inactive Laptop')
            ->assertDontSee('Hidden Root');
    }

    public function test_header_greets_signed_in_customer(): void
    {
        $user = User::factory()->create(['name' => 'Ashik Rahman']);

        $this->actingAs($user)
            ->get(route('store.home'))
            ->assertOk()
            ->assertSee('Hi, Ashik')
            ->assertDontSee('Register or Login');
    }

    public function test_search_suggestions_return_matching_published_products(): void
    {
        Product::factory()->create(['name' => 'Gigabyte RTX 5070 Ti Gaming OC']);
        Product::factory()->draft()->create(['name' => 'Draft RTX 5070 Card']);
        Product::factory()->create(['name' => 'Walton Refrigerator']);

        $this->getJson(route('store.search.suggest', ['q' => 'RTX']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Gigabyte RTX 5070 Ti Gaming OC')
            ->assertJsonStructure(['data' => [['name', 'url', 'image', 'brand', 'price', 'regular_price', 'discount_percent', 'in_stock', 'stock_label']]])
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.search_url', route('store.search', ['q' => 'RTX']));
    }

    public function test_search_suggestions_include_brand_discount_and_stock(): void
    {
        $brand = Brand::factory()->create(['name' => 'Gigabyte']);
        Product::factory()->priced('15000', '12000')->create(['name' => 'Gigabyte RTX 5060', 'brand_id' => $brand->id]);

        $this->getJson(route('store.search.suggest', ['q' => 'RTX']))
            ->assertOk()
            ->assertJsonPath('data.0.brand', 'Gigabyte')
            ->assertJsonPath('data.0.regular_price', Money::format('15000'))
            ->assertJsonPath('data.0.price', Money::format('12000'))
            ->assertJsonPath('data.0.discount_percent', 20)
            ->assertJsonPath('data.0.in_stock', true);
    }

    public function test_search_suggestions_include_matching_active_categories(): void
    {
        Category::factory()->create(['name' => 'Gaming Laptop', 'slug' => 'gaming-laptop']);
        Category::factory()->create(['name' => 'Gaming Monitor Hidden', 'is_active' => false]);
        Category::factory()->create(['name' => 'Refrigerator']);

        $this->getJson(route('store.search.suggest', ['q' => 'gaming']))
            ->assertOk()
            ->assertJsonCount(1, 'categories')
            ->assertJsonPath('categories.0.name', 'Gaming Laptop')
            ->assertJsonPath('categories.0.url', route('store.categories.show', 'gaming-laptop'));
    }

    public function test_search_suggestions_ignore_short_terms(): void
    {
        $this->getJson(route('store.search.suggest', ['q' => 'a']))
            ->assertOk()
            ->assertExactJson(['data' => []]);
    }
}
