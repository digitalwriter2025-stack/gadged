<?php

namespace Tests\Feature\Database;

use App\Enums\ProductType;
use App\Enums\StaffRole;
use App\Models\Category;
use App\Models\District;
use App\Models\FlashSale;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingRate;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_builds_a_consistent_catalog_and_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);

        $counts = fn (): array => [
            Product::query()->count(),
            ProductVariant::query()->count(),
            InventoryTransaction::query()->count(),
            Category::query()->count(),
            User::query()->count(),
        ];
        $first = $counts();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($first, $counts());
        $this->assertGreaterThanOrEqual(20, $first[0]);

        $this->assertSame(64, District::query()->count());
        $this->assertSame(1, District::query()->whereHas('shippingZone', fn ($q) => $q->where('code', 'inside_dhaka'))->count());
        $this->assertSame(2, ShippingRate::query()->count());

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $this->assertTrue($admin->hasRole(StaffRole::SuperAdmin->value));

        // Every product has a default variant and its stock matches the ledger.
        $this->assertSame(0, Product::query()->whereDoesntHave('defaultVariant')->count());

        foreach (ProductVariant::query()->get() as $variant) {
            $this->assertSame(
                (int) InventoryTransaction::query()->where('product_variant_id', $variant->id)->sum('quantity'),
                $variant->stock_quantity,
                "Ledger mismatch for {$variant->sku}",
            );
        }

        $variable = Product::query()->where('type', ProductType::Variable)->with('variants.attributeValues')->firstOrFail();
        $this->assertGreaterThan(1, $variable->variants->count());
        $this->assertCount(3, $variable->variants->first()->attributeValues);

        $this->assertTrue(FlashSale::query()->running()->exists());
        $this->assertSame(0, Category::query()->where('path', '/')->count());
    }

    public function test_every_homepage_product_card_links_to_a_product_page(): void
    {
        $this->seed(DatabaseSeeder::class);

        $html = $this->get(route('store.home'))
            ->assertOk()
            ->assertSeeInOrder([
                'Featured Categories', 'Flash Sale', 'Mobile Phones', 'Smart Watches', 'Earbuds',
                'Select AMD-powered gaming devices', 'Featured Products', 'New and Noteworthy',
                'Latest Products', 'Best Sellers', 'All Product',
            ])
            ->getContent();

        preg_match_all('#href="('.preg_quote(url('/product/'), '#').'[^"]+)"#', $html, $matches);

        $this->assertNotEmpty($matches[1]);

        foreach (array_unique($matches[1]) as $url) {
            $this->get($url)->assertOk();
        }
    }
}
