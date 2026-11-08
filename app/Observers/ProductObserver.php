<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\CatalogCache;

class ProductObserver
{
    public function __construct(private readonly CatalogCache $cache) {}

    /**
     * The primary category is always part of category_product so category listings
     * need a single pivot lookup.
     */
    public function saved(Product $product): void
    {
        if ($product->wasRecentlyCreated || $product->wasChanged('category_id')) {
            $product->categories()->syncWithoutDetaching([$product->category_id]);
        }

        if ($product->wasChanged(['status', 'published_at', 'is_featured', 'is_latest', 'is_best_seller', 'name', 'slug'])) {
            $this->cache->flush();
        }
    }

    public function deleted(Product $product): void
    {
        $this->cache->flush();
    }

    public function restored(Product $product): void
    {
        $this->cache->flush();
    }
}
