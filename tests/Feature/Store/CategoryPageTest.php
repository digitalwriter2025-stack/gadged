<?php

namespace Tests\Feature\Store;

use App\Enums\SpecificationInputType;
use App\Enums\StockStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Specification;
use App\Models\SpecificationGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_page_shows_heading_breadcrumbs_subcategories_and_subtree_products(): void
    {
        $parent = Category::factory()->create(['name' => 'Component']);
        $child = Category::factory()->childOf($parent)->create(['name' => 'Graphics Card']);
        Category::factory()->childOf($parent)->inactive()->create(['name' => 'Hidden Child']);

        Product::factory()->priced('124000.00', '118000.00')->create(['name' => 'Child RTX Card', 'category_id' => $child->id]);
        Product::factory()->draft()->create(['name' => 'Draft Card', 'category_id' => $child->id]);
        Product::factory()->create(['name' => 'Unrelated Fridge']);

        $response = $this->get(route('store.categories.show', $parent->slug));

        $response->assertOk()
            ->assertSee('Component Price in Bangladesh')
            ->assertSeeInOrder(['Home', 'Component'])
            ->assertSee(route('store.categories.show', $child->slug))
            ->assertDontSee('Hidden Child')
            ->assertSee('Child RTX Card')
            ->assertSee('Save: ৳6,000')
            ->assertSee('Add to Cart')
            ->assertDontSee('Draft Card')
            ->assertDontSee('Unrelated Fridge')
            ->assertSee('"@type":"ItemList"', false);
    }

    public function test_inactive_and_missing_categories_return_not_found(): void
    {
        $inactive = Category::factory()->inactive()->create();

        $this->get(route('store.categories.show', $inactive->slug))->assertNotFound();
        $this->get(route('store.categories.show', 'missing-category'))->assertNotFound();
    }

    public function test_products_filter_by_stock_price_brand_and_search(): void
    {
        $category = Category::factory()->create();
        $asus = Brand::factory()->create(['name' => 'Asus', 'slug' => 'asus']);
        $msi = Brand::factory()->create(['name' => 'Msi', 'slug' => 'msi']);

        Product::factory()->priced('50000.00')->create(['name' => 'Asus Cheap Card', 'category_id' => $category->id, 'brand_id' => $asus->id]);
        Product::factory()->priced('90000.00')->create(['name' => 'Msi Pricey Card', 'category_id' => $category->id, 'brand_id' => $msi->id]);
        Product::factory()->priced('70000.00', null, 0)->create(['name' => 'Msi Sold Card', 'category_id' => $category->id, 'brand_id' => $msi->id, 'stock_status' => StockStatus::OutOfStock]);

        $url = fn (array $query): string => route('store.categories.show', ['category' => $category->slug, ...$query]);

        $this->get($url(['brand' => ['msi']]))->assertOk()
            ->assertSee('Msi Pricey Card')->assertSee('Msi Sold Card')->assertDontSee('Asus Cheap Card');

        $this->get($url(['stock' => ['in_stock']]))->assertOk()
            ->assertSee('Asus Cheap Card')->assertSee('Msi Pricey Card')->assertDontSee('Msi Sold Card');

        $this->get($url(['min' => 60000, 'max' => 95000]))->assertOk()
            ->assertSee('Msi Pricey Card')->assertDontSee('Asus Cheap Card');

        $this->get($url(['q' => 'pricey']))->assertOk()
            ->assertSee('Msi Pricey Card')->assertDontSee('Asus Cheap Card')
            ->assertSee('noindex,follow', false);

        $this->get($url(['sort' => 'price_desc']))->assertOk()
            ->assertSeeInOrder(['Msi Pricey Card', 'Msi Sold Card', 'Asus Cheap Card']);
    }

    public function test_products_filter_by_specification_options_and_numbers(): void
    {
        $category = Category::factory()->create();
        $group = SpecificationGroup::query()->create(['name' => 'Graphics', 'slug' => 'graphics']);
        $memoryType = Specification::query()->create(['specification_group_id' => $group->id, 'name' => 'Memory Type', 'slug' => 'memory-type', 'input_type' => SpecificationInputType::Select, 'is_filterable' => true]);
        $memorySize = Specification::query()->create(['specification_group_id' => $group->id, 'name' => 'Memory Size', 'slug' => 'memory-size', 'input_type' => SpecificationInputType::Number, 'unit' => 'GB', 'is_filterable' => true]);
        $category->specifications()->attach([$memoryType->id => ['is_filterable' => true, 'sort_order' => 1], $memorySize->id => ['is_filterable' => true, 'sort_order' => 2]]);
        $gddr7 = $memoryType->options()->create(['value' => 'GDDR7', 'slug' => 'gddr7']);
        $gddr6 = $memoryType->options()->create(['value' => 'GDDR6', 'slug' => 'gddr6']);

        $fast = Product::factory()->create(['name' => 'Fast Card', 'category_id' => $category->id]);
        $fast->specificationValues()->createMany([
            ['specification_id' => $memoryType->id, 'specification_option_id' => $gddr7->id],
            ['specification_id' => $memorySize->id, 'value' => '16', 'value_numeric' => '16'],
        ]);
        $slow = Product::factory()->create(['name' => 'Slow Card', 'category_id' => $category->id]);
        $slow->specificationValues()->createMany([
            ['specification_id' => $memoryType->id, 'specification_option_id' => $gddr6->id],
            ['specification_id' => $memorySize->id, 'value' => '8', 'value_numeric' => '8'],
        ]);

        $url = fn (array $query): string => route('store.categories.show', ['category' => $category->slug, ...$query]);

        $this->get(route('store.categories.show', $category->slug))->assertOk()
            ->assertSeeInOrder(['Availability', 'Price Range', 'Brands', 'Memory Type', 'GDDR7', 'Memory Size', '8 GB', '16 GB'])
            ->assertSee('Memory Type: GDDR7');

        $this->get($url(['spec' => ['memory-type' => ['gddr7']]]))->assertOk()
            ->assertSee('Fast Card')->assertDontSee('Slow Card');

        $this->get($url(['spec' => ['memory-size' => ['8']]]))->assertOk()
            ->assertSee('Slow Card')->assertDontSee('Fast Card');

        $this->get($url(['spec' => ['memory-type' => ['unknown']], 'sort' => 'bogus', 'stock' => ['nope']]))->assertOk()
            ->assertSee('Fast Card')->assertSee('Slow Card');
    }

    public function test_active_filters_render_chips_that_each_remove_one_filter(): void
    {
        $category = Category::factory()->create();
        $asus = Brand::factory()->create(['name' => 'Asus', 'slug' => 'asus']);
        $msi = Brand::factory()->create(['name' => 'Msi', 'slug' => 'msi']);
        Product::factory()->priced('50000.00')->create(['category_id' => $category->id, 'brand_id' => $asus->id]);
        Product::factory()->priced('90000.00')->create(['category_id' => $category->id, 'brand_id' => $msi->id]);

        $base = route('store.categories.show', $category->slug);

        $this->get($base)->assertOk()->assertDontSee('data-active-filters', false);

        $this->get($base.'?brand[]=asus&brand[]=msi&min=60000&page=2')->assertOk()
            ->assertSee('data-active-filters', false)
            ->assertSee('Remove filter Brands: Asus')
            ->assertSee($base.'?brand%5B0%5D=msi&min=60000')
            ->assertSee($base.'?brand%5B0%5D=asus&min=60000')
            ->assertSee('৳60,000 – ৳90,000')
            ->assertSee($base.'?brand%5B0%5D=asus&brand%5B1%5D=msi');
    }

    public function test_long_option_lists_get_a_search_box(): void
    {
        $short = Category::factory()->create();
        Product::factory()->count(2)->create(['category_id' => $short->id]);

        $long = Category::factory()->create();
        Product::factory()->count(9)->sequence(fn ($sequence): array => ['brand_id' => Brand::factory()->create(['name' => 'Brand '.$sequence->index])->id])
            ->create(['category_id' => $long->id]);

        $this->get(route('store.categories.show', $short->slug))->assertOk()->assertDontSee('data-filter-search', false);
        $this->get(route('store.categories.show', $long->slug))->assertOk()->assertSee('data-filter-search', false)->assertSee('Search Brands');
    }

    public function test_listing_is_paginated_with_query_string(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(21)->create(['category_id' => $category->id]);

        $this->get(route('store.categories.show', ['category' => $category->slug, 'sort' => 'newest']))
            ->assertOk()
            ->assertSee('Showing 1–20 of 21 products')
            ->assertSee('sort=newest&amp;page=2', false);
    }
}
