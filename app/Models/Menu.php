<?php

namespace App\Models;

use App\Observers\CatalogCacheObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Named link list rendered at a fixed location (header, footer_information, ...).
 */
#[Fillable(['name', 'location'])]
#[ObservedBy(CatalogCacheObserver::class)]
class Menu extends Model
{
    /**
     * @return HasMany<MenuItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function rootItems(): HasMany
    {
        return $this->items()->whereNull('parent_id');
    }
}
