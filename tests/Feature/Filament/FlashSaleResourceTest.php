<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\FlashSaleDiscountType;
use App\Filament\Resources\FlashSales\Pages\CreateFlashSale;
use App\Filament\Resources\FlashSales\Pages\EditFlashSale;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use App\Models\User;
use Filament\Forms\Components\Repeater;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FlashSaleResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->superAdmin()->create());
    }

    public function test_admin_can_create_a_flash_sale_with_every_discount_type(): void
    {
        $undoRepeaterFake = Repeater::fake();

        $headphone = Product::factory()->priced('3600.00')->create();
        $watch = Product::factory()->priced('8000.00')->create();
        $speaker = Product::factory()->priced('2000.00')->create();

        Livewire::test(CreateFlashSale::class)
            ->fillForm([
                'title' => 'Eid Flash Sale',
                'slug' => 'eid-flash-sale',
                'starts_at' => now(),
                'ends_at' => now()->addDays(3),
                'items' => [
                    ['product_id' => $headphone->id, 'discount_type' => FlashSaleDiscountType::Percent->value, 'discount_value' => '10'],
                    ['product_id' => $watch->id, 'discount_type' => FlashSaleDiscountType::Amount->value, 'discount_value' => '500', 'quantity_limit' => 20],
                    ['product_id' => $speaker->id, 'discount_type' => FlashSaleDiscountType::FixedPrice->value, 'discount_value' => '1499'],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $undoRepeaterFake();

        $items = FlashSale::query()->where('slug', 'eid-flash-sale')->sole()->items()->with('product')->get();

        $this->assertSame(
            [[$headphone->id, '3240.00', null], [$watch->id, '7500.00', 20], [$speaker->id, '1499.00', null]],
            $items->map(fn (FlashSaleProduct $item): array => [
                $item->product_id,
                $item->priceFrom($item->product->regular_price),
                $item->quantity_limit,
            ])->all(),
        );
    }

    public function test_each_deal_previews_its_storefront_price(): void
    {
        $item = FlashSaleProduct::factory()
            ->for(Product::factory()->priced('3600.00')->state(['name' => 'Redragon H320 Lamia']))
            ->create(['discount_type' => FlashSaleDiscountType::Percent, 'discount_value' => '10']);

        Livewire::test(EditFlashSale::class, ['record' => $item->flash_sale_id])
            ->assertOk()
            ->assertSee('Redragon H320 Lamia')
            ->assertSee('৳3,600 → ৳3,240');
    }

    public function test_sale_must_end_after_it_starts(): void
    {
        Livewire::test(CreateFlashSale::class)
            ->fillForm([
                'title' => 'Backwards Sale',
                'slug' => 'backwards-sale',
                'starts_at' => now()->addDay(),
                'ends_at' => now(),
            ])
            ->call('create')
            ->assertHasFormErrors(['ends_at' => 'after']);
    }

    public function test_percentage_discount_cannot_exceed_one_hundred(): void
    {
        $undoRepeaterFake = Repeater::fake();

        Livewire::test(CreateFlashSale::class)
            ->fillForm([
                'title' => 'Too Generous Sale',
                'slug' => 'too-generous-sale',
                'items' => [
                    ['product_id' => Product::factory()->create()->id, 'discount_type' => FlashSaleDiscountType::Percent->value, 'discount_value' => '150'],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['items.0.discount_value' => 'max']);

        $undoRepeaterFake();
    }

    public function test_the_same_product_cannot_be_added_twice(): void
    {
        $undoRepeaterFake = Repeater::fake();

        $product = Product::factory()->create();

        Livewire::test(CreateFlashSale::class)
            ->fillForm([
                'title' => 'Double Deal Sale',
                'slug' => 'double-deal-sale',
                'items' => [
                    ['product_id' => $product->id, 'discount_type' => FlashSaleDiscountType::Percent->value, 'discount_value' => '10'],
                    ['product_id' => $product->id, 'discount_type' => FlashSaleDiscountType::Percent->value, 'discount_value' => '20'],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['items.1.product_id']);

        $undoRepeaterFake();
    }
}
