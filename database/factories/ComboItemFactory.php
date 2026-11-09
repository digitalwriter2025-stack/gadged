<?php

namespace Database\Factories;

use App\Models\ComboItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComboItem>
 */
class ComboItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'combo_id' => Product::factory()->combo(),
            'product_id' => Product::factory(),
            'quantity' => 1,
            'sort_order' => 0,
        ];
    }
}
