<?php

namespace Tests\Feature\Store;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_brands_page_lists_active_brands_with_published_products(): void
    {
        $apple = Brand::factory()->create(['name' => 'Apple Listed']);
        $samsung = Brand::factory()->create(['name' => 'Samsung Listed']);
        Product::factory()->for($apple)->create();
        Product::factory()->for($samsung)->create();

        $this->get(route('store.brands.index'))
            ->assertOk()
            ->assertSeeInOrder(['Apple Listed', 'Samsung Listed'])
            ->assertSee(route('store.search', ['q' => 'Apple Listed']), false);
    }

    public function test_brands_page_hides_inactive_and_empty_brands(): void
    {
        $inactive = Brand::factory()->create(['name' => 'Inactive Brand', 'is_active' => false]);
        $draftOnly = Brand::factory()->create(['name' => 'Draft Only Brand']);
        Brand::factory()->create(['name' => 'Empty Brand']);
        Product::factory()->for($inactive)->create();
        Product::factory()->for($draftOnly)->draft()->create();

        $this->get(route('store.brands.index'))
            ->assertOk()
            ->assertDontSee('Inactive Brand')
            ->assertDontSee('Draft Only Brand')
            ->assertDontSee('Empty Brand');
    }
}
