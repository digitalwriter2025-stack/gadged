<?php

namespace App\Models;

use App\Enums\FlashSaleDiscountType;
use App\Support\Money;
use Database\Factories\FlashSaleProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A product (or one of its variants) inside a flash sale.
 * sold_quantity is not fillable; it is incremented atomically at checkout.
 */
#[Fillable([
    'flash_sale_id', 'product_id', 'product_variant_id', 'discount_type', 'discount_value',
    'quantity_limit', 'per_order_limit', 'sort_order',
])]
class FlashSaleProduct extends Model
{
    /** @use HasFactory<FlashSaleProductFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discount_type' => FlashSaleDiscountType::class,
            'discount_value' => 'decimal:2',
            'quantity_limit' => 'integer',
            'sold_quantity' => 'integer',
            'per_order_limit' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function remainingQuantity(): ?int
    {
        return $this->quantity_limit === null ? null : max(0, $this->quantity_limit - $this->sold_quantity);
    }

    public function isSoldOut(): bool
    {
        return $this->remainingQuantity() === 0;
    }

    /**
     * Deal price derived from a regular price; never below zero.
     */
    public function priceFrom(string $regularPrice): string
    {
        $price = match ($this->discount_type) {
            FlashSaleDiscountType::FixedPrice => Money::of($this->discount_value),
            FlashSaleDiscountType::Amount => Money::sub($regularPrice, $this->discount_value),
            FlashSaleDiscountType::Percent => Money::sub($regularPrice, Money::percentOf($regularPrice, $this->discount_value)),
        };

        return Money::floor0(Money::min($price, $regularPrice));
    }

    /**
     * @return BelongsTo<FlashSale, $this>
     */
    public function flashSale(): BelongsTo
    {
        return $this->belongsTo(FlashSale::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
