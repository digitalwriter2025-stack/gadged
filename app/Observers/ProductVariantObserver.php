<?php

namespace App\Observers;

use App\Models\ProductVariant;
use App\Services\ProductAggregateService;

/**
 * Keeps the product's price / stock read model in sync with admin edits to variants.
 */
class ProductVariantObserver
{
    public function __construct(private readonly ProductAggregateService $aggregates) {}

    public function saved(ProductVariant $variant): void
    {
        if ($variant->wasRecentlyCreated || $variant->wasChanged([
            'price', 'sale_price', 'sale_starts_at', 'sale_ends_at', 'is_active', 'deleted_at',
        ])) {
            $this->aggregates->refresh($variant->product_id);
        }
    }

    public function deleted(ProductVariant $variant): void
    {
        $this->aggregates->refresh($variant->product_id);
    }

    public function restored(ProductVariant $variant): void
    {
        $this->aggregates->refresh($variant->product_id);
    }
}
