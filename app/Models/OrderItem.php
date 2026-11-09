<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Order line. Product, SKU, variation and price are snapshots; the product
 * relations are kept only for linking and may be null after deletion.
 */
#[Fillable([
    'order_id', 'product_id', 'product_variant_id', 'flash_sale_product_id',
    'product_name', 'sku', 'variant_name', 'variant_options', 'image_path', 'warranty',
    'regular_price', 'unit_price', 'unit_discount', 'unit_cost', 'quantity', 'line_total',
])]
class OrderItem extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'variant_options' => 'array',
            'regular_price' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'unit_discount' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'line_total' => 'decimal:2',
            'quantity' => 'integer',
            'returned_quantity' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id')->withTrashed();
    }

    /**
     * @return BelongsTo<FlashSaleProduct, $this>
     */
    public function flashSaleProduct(): BelongsTo
    {
        return $this->belongsTo(FlashSaleProduct::class);
    }
}
