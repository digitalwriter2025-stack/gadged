<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory()->variable(),
            'sku' => 'VAR-'.strtoupper(Str::random(10)),
            'name' => null,
            'combination_key' => (string) fake()->unique()->numberBetween(1, 99999999),
            'price' => '1000.00',
            'sale_price' => null,
            'stock_quantity' => 10,
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function stock(int $quantity): static
    {
        return $this->state(fn (array $attributes) => ['stock_quantity' => $quantity]);
    }
}
