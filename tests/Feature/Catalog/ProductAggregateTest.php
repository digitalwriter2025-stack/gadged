<?php

namespace Tests\Feature\Catalog;

use App\Enums\ReviewStatus;
use App\Enums\StockStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Services\ProductAggregateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductAggregateTest extends TestCase
{
    use RefreshDatabase;

    public function test_simple_product_read_model_follows_its_variant(): void
    {
        $product = Product::factory()->priced('3600', '2949', stock: 25)->create()->fresh();

        $this->assertSame('2949.00', $product->min_price);
        $this->assertSame('2949.00', $product->max_price);
        $this->assertSame('3600.00', $product->regular_price);
        $this->assertSame(18, $product->discount_percent);
        $this->assertSame(25, $product->total_stock);
    }

    public function test_variable_product_uses_price_range_of_active_variants(): void
    {
        $product = Product::factory()->variable()->create();
        ProductVariant::factory()->for($product)->create(['price' => '52999', 'stock_quantity' => 2]);
        ProductVariant::factory()->for($product)->create(['price' => '57999', 'sale_price' => '55999', 'stock_quantity' => 3]);
        ProductVariant::factory()->for($product)->create(['price' => '10', 'is_active' => false, 'stock_quantity' => 50]);

        $product->refresh();

        $this->assertSame('52999.00', $product->min_price);
        $this->assertSame('55999.00', $product->max_price);
        $this->assertSame('52999.00', $product->regular_price);
        $this->assertSame(3, $product->discount_percent);
        $this->assertSame(5, $product->total_stock);
    }

    public function test_sale_price_outside_its_window_is_ignored(): void
    {
        $product = Product::factory()->create();
        $product->defaultVariant->update([
            'price' => '1000',
            'sale_price' => '800',
            'sale_starts_at' => now()->addDay(),
        ]);

        $product->refresh();
        $this->assertSame('1000.00', $product->min_price);
        $this->assertSame(0, $product->discount_percent);

        $product->defaultVariant->update(['sale_starts_at' => now()->subDay(), 'sale_ends_at' => now()->subMinute()]);
        $this->assertSame('1000.00', $product->fresh()->min_price);

        $product->defaultVariant->update(['sale_ends_at' => now()->addDay()]);
        $this->assertSame('800.00', $product->fresh()->min_price);
    }

    public function test_editorial_stock_states_are_not_overwritten(): void
    {
        $product = Product::factory()->priced('100', stock: 0)->create(['stock_status' => StockStatus::PreOrder]);

        app(ProductAggregateService::class)->refresh($product);

        $this->assertSame(StockStatus::PreOrder, $product->fresh()->stock_status);
    }

    public function test_rating_counts_only_approved_reviews(): void
    {
        $product = Product::factory()->create();
        Review::factory()->for($product)->approved()->create(['rating' => 5]);
        Review::factory()->for($product)->approved()->create(['rating' => 4]);
        Review::factory()->for($product)->create(['rating' => 1, 'status' => ReviewStatus::Pending]);

        app(ProductAggregateService::class)->refreshRating($product);

        $product->refresh();
        $this->assertSame(2, $product->rating_count);
        $this->assertSame('4.50', $product->rating_avg);
    }

    public function test_published_scope_hides_drafts_and_future_products(): void
    {
        $visible = Product::factory()->create();
        Product::factory()->draft()->create();
        Product::factory()->create(['published_at' => now()->addDay()]);

        $this->assertSame([$visible->id], Product::query()->published()->pluck('id')->all());
    }

    public function test_product_card_listing_uses_a_constant_number_of_queries(): void
    {
        Product::factory()->count(12)->create();

        DB::enableQueryLog();

        $cards = Product::query()->published()->forCard()->latest('published_at')->limit(12)->get();
        $cards->each(fn (Product $product) => [$product->brand->name, $product->getFirstMediaUrl('main', 'card')]);

        $this->assertCount(12, $cards);
        $this->assertCount(3, DB::getQueryLog(), 'products + brands + media');
    }

    public function test_cost_price_is_hidden_from_serialization(): void
    {
        $variant = Product::factory()->create()->defaultVariant;
        $variant->update(['cost_price' => '700']);

        $this->assertArrayNotHasKey('cost_price', $variant->fresh()->toArray());
    }
}
