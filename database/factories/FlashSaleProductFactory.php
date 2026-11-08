<?php

namespace Database\Factories;

use App\Enums\FlashSaleDiscountType;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FlashSaleProduct>
 */
class FlashSaleProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'flash_sale_id' => FlashSale::factory(),
            'product_id' => Product::factory(),
            'product_variant_id' => null,
            'discount_type' => FlashSaleDiscountType::Percent,
            'discount_value' => '10.00',
            'quantity_limit' => null,
            'sold_quantity' => 0,
            'sort_order' => 0,
        ];
    }
}
