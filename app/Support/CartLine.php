<?php

namespace App\Support;

use App\Models\FlashSaleProduct;
use App\Models\Product;
use App\Models\ProductVariant;

/**
 * One priced cart line. Prices are always recomputed from the catalog, never
 * taken from the session or the browser.
 */
final readonly class CartLine
{
    public function __construct(
        public Product $product,
        public ProductVariant $variant,
        public int $quantity,
        public string $regularPrice,
        public string $unitPrice,
        public ?FlashSaleProduct $flashSaleItem = null,
    ) {}

    public function lineTotal(): string
    {
        return Money::mul($this->unitPrice, $this->quantity);
    }

    /**
     * Saving per unit against the regular price.
     */
    public function unitDiscount(): string
    {
        return Money::floor0(Money::sub($this->regularPrice, $this->unitPrice));
    }
}
