<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\ProductStatus;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->superAdmin()->create());
    }

    public function test_product_list_shows_price_discount_and_stock(): void
    {
        $product = Product::factory()->priced('2000.00', '1500.00', 3)->create(['name' => 'Test Earbuds']);

        Livewire::test(ListProducts::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$product])
            ->assertSee('Test Earbuds')
            ->assertSee('-25%')
            ->assertSee('Only 3 left');
    }

    public function test_stock_tabs_filter_the_list(): void
    {
        $inStock = Product::factory()->priced('1000.00', null, 20)->create();
        $lowStock = Product::factory()->priced('1000.00', null, 2)->create();
        $outOfStock = Product::factory()->priced('1000.00', null, 0)->create();

        Livewire::test(ListProducts::class)
            ->set('activeTab', 'low_stock')
            ->assertCanSeeTableRecords([$lowStock])
            ->assertCanNotSeeTableRecords([$inStock, $outOfStock])
            ->set('activeTab', 'out_of_stock')
            ->assertCanSeeTableRecords([$outOfStock])
            ->assertCanNotSeeTableRecords([$inStock, $lowStock]);
    }

    public function test_status_tabs_filter_the_list(): void
    {
        $active = Product::factory()->create();
        $draft = Product::factory()->draft()->create();

        Livewire::test(ListProducts::class)
            ->set('activeTab', 'draft')
            ->assertCanSeeTableRecords([$draft])
            ->assertCanNotSeeTableRecords([$active]);
    }

    public function test_featured_can_be_toggled_from_the_list(): void
    {
        $product = Product::factory()->create(['is_featured' => false]);

        Livewire::test(ListProducts::class)
            ->call('updateTableColumnState', 'is_featured', (string) $product->getKey(), true);

        $this->assertTrue($product->refresh()->is_featured);
    }

    public function test_product_is_created_with_price_and_stock_on_its_default_variant(): void
    {
        $category = Category::factory()->create();

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Walnut Bookshelf Speaker',
                'slug' => 'walnut-bookshelf-speaker',
                'sku' => 'GD-SP-09',
                'category_id' => $category->id,
                'selling_price' => '1500',
                'compare_at_price' => '2000',
                'stock_quantity' => 7,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::query()->where('slug', 'walnut-bookshelf-speaker')->firstOrFail();
        $variant = $product->defaultVariant()->firstOrFail();

        $this->assertSame(ProductStatus::Active, $product->status);
        $this->assertSame('2000.00', $variant->price);
        $this->assertSame('1500.00', $variant->sale_price);
        $this->assertSame(7, $variant->stock_quantity);
        $this->assertSame('1500.00', $product->min_price);
        $this->assertSame(25, $product->discount_percent);
        $this->assertSame(7, $product->total_stock);
    }

    public function test_edit_form_loads_and_updates_price_and_stock(): void
    {
        $product = Product::factory()->priced('2000.00', '1500.00', 3)->create();

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->assertSchemaStateSet([
                'selling_price' => '1500.00',
                'compare_at_price' => '2000.00',
                'stock_quantity' => 3,
            ])
            ->fillForm([
                'selling_price' => '1800',
                'compare_at_price' => null,
                'stock_quantity' => 12,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $variant = $product->defaultVariant()->firstOrFail();

        $this->assertSame('1800.00', $variant->price);
        $this->assertNull($variant->sale_price);
        $this->assertSame(12, $variant->stock_quantity);
        $this->assertSame(0, $product->refresh()->discount_percent);
    }

    public function test_regular_price_must_be_higher_than_selling_price(): void
    {
        $product = Product::factory()->create();

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm([
                'selling_price' => '1500',
                'compare_at_price' => '1200',
            ])
            ->call('save')
            ->assertHasFormErrors(['compare_at_price' => 'gt']);
    }

    public function test_selected_products_can_be_moved_to_draft(): void
    {
        $products = Product::factory()->count(2)->create();

        Livewire::test(ListProducts::class)
            ->selectTableRecords($products)
            ->callAction(TestAction::make('markDraft')->table()->bulk());

        $products->each(fn (Product $product) => $this->assertSame(ProductStatus::Draft, $product->refresh()->status));
    }
}
