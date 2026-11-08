<?php

namespace App\Observers;

use App\Services\CatalogCache;
use Illuminate\Database\Eloquent\Model;

/**
 * Invalidates the storefront catalog cache whenever admin-managed content changes.
 */
class CatalogCacheObserver
{
    public function __construct(private readonly CatalogCache $cache) {}

    public function saved(Model $model): void
    {
        $this->cache->flush();
    }

    public function deleted(Model $model): void
    {
        $this->cache->flush();
    }

    public function restored(Model $model): void
    {
        $this->cache->flush();
    }
}
