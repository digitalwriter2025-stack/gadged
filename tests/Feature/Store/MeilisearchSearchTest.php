<?php

namespace Tests\Feature\Store;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\Engine;
use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class MeilisearchSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_lists_products_in_meilisearch_relevance_order(): void
    {
        $tab = Product::factory()->create(['name' => 'Samsung Galaxy Tab S10', 'is_featured' => true]);
        $phone = Product::factory()->create(['name' => 'Samsung Galaxy S25 Ultra']);
        $draft = Product::factory()->draft()->create(['name' => 'Samsung Draft Phone']);
        Product::factory()->create(['name' => 'Logitech Mouse']);

        $this->useMeilisearchReturning([$phone->id, $draft->id, $tab->id]);

        $this->get(route('store.search', ['q' => 'samsng']))
            ->assertOk()
            ->assertSee('2 products found')
            ->assertSeeInOrder(['Samsung Galaxy S25 Ultra', 'Samsung Galaxy Tab S10'])
            ->assertDontSee('Samsung Draft Phone')
            ->assertDontSee('Logitech Mouse');
    }

    public function test_explicit_sort_overrides_relevance_order(): void
    {
        $cheap = Product::factory()->create(['name' => 'Budget Earbuds', 'min_price' => 1000]);
        $pricey = Product::factory()->create(['name' => 'Premium Earbuds', 'min_price' => 9000]);

        $this->useMeilisearchReturning([$pricey->id, $cheap->id]);

        $this->get(route('store.search', ['q' => 'earbuds', 'sort' => 'price_asc']))
            ->assertOk()
            ->assertSeeInOrder(['Budget Earbuds', 'Premium Earbuds']);
    }

    public function test_suggestions_use_meilisearch_relevance_order(): void
    {
        $featured = Product::factory()->create(['name' => 'Galaxy Buds Featured', 'is_featured' => true]);
        $best = Product::factory()->create(['name' => 'Samsung Galaxy S25']);

        $this->useMeilisearchReturning([$best->id, $featured->id]);

        $this->getJson(route('store.search.suggest', ['q' => 'galaxy']))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Samsung Galaxy S25')
            ->assertJsonPath('data.1.name', 'Galaxy Buds Featured')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_search_falls_back_to_database_when_meilisearch_is_unreachable(): void
    {
        Product::factory()->create(['name' => 'Gigabyte RTX 5070']);
        Product::factory()->create(['name' => 'Logitech Mouse']);

        $this->useMeilisearch(fn (MockInterface $engine) => $engine->shouldReceive('keys')
            ->andThrow(new RuntimeException('Connection refused')));

        $this->getJson(route('store.search.suggest', ['q' => 'RTX']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Gigabyte RTX 5070');
    }

    public function test_searchable_document_includes_brand_category_and_bangla_name(): void
    {
        $product = Product::factory()->create(['name' => 'Galaxy S25', 'name_bn' => 'গ্যালাক্সি এস২৫']);

        $document = $product->fresh()->toSearchableArray();

        $this->assertSame('গ্যালাক্সি এস২৫', $document['name_bn']);
        $this->assertSame($product->brand?->name, $document['brand']);
        $this->assertSame($product->category->name, $document['category']);
    }

    /**
     * @param  list<int>  $ids
     */
    private function useMeilisearchReturning(array $ids): void
    {
        $this->useMeilisearch(fn (MockInterface $engine) => $engine->shouldReceive('keys')
            ->andReturn(collect($ids)));
    }

    /**
     * Switch Scout to the meilisearch driver, backed by a mocked engine.
     *
     * @param  callable(MockInterface): mixed  $expectations
     */
    private function useMeilisearch(callable $expectations): void
    {
        $engine = Mockery::mock(Engine::class)->shouldIgnoreMissing();
        $expectations($engine);

        config(['scout.driver' => 'meilisearch']);
        $this->app->make(EngineManager::class)->extend('meilisearch', fn (): Engine => $engine);
    }
}
