<?php

namespace Database\Factories;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\StockStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductAggregateService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Simple products automatically receive their default variant (price 1,000, stock 10)
 * unless the variable() state is used. Use priced() to change price / stock.
 *
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(3, true));

        return [
            'type' => ProductType::Simple,
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 999999),
            'sku' => 'SKU-'.strtoupper(Str::random(10)),
            'model' => strtoupper(fake()->bothify('??-####')),
            'brand_id' => Brand::factory(),
            'category_id' => Category::factory(),
            'short_description' => fake()->sentence(),
            'warranty' => '1 Year',
            'condition' => ProductCondition::New,
            'manage_stock' => true,
            'stock_status' => StockStatus::InStock,
            'status' => ProductStatus::Active,
            'published_at' => now()->subDay(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Product $product): void {
            if ($product->type !== ProductType::Simple || $product->variants()->exists()) {
                return;
            }

            ProductVariant::factory()->for($product)->create([
                'sku' => $product->sku,
                'is_default' => true,
                'price' => '1000.00',
                'stock_quantity' => 10,
            ]);
        });
    }

    public function variable(): static
    {
        return $this->state(fn (array $attributes) => ['type' => ProductType::Variable]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['status' => ProductStatus::Draft]);
    }

    public function unmanagedStock(): static
    {
        return $this->state(fn (array $attributes) => ['manage_stock' => false]);
    }

    /**
     * Set the default variant's price, sale price and stock after creation.
     */
    public function priced(string $price, ?string $salePrice = null, int $stock = 10): static
    {
        return $this->afterCreating(function (Product $product) use ($price, $salePrice, $stock): void {
            $variant = $product->defaultVariant()->firstOrFail();

            $variant->forceFill([
                'price' => $price,
                'sale_price' => $salePrice,
                'stock_quantity' => $stock,
            ])->save();

            app(ProductAggregateService::class)->refresh($product);
        });
    }
}
