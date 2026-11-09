<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Observers\CatalogCacheObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Item in the service strip ("0% EMI", "Cash on delivery in 64 districts").
 */
#[Fillable(['icon', 'title', 'subtitle', 'url', 'is_active', 'sort_order', 'title_bn', 'subtitle_bn'])]
#[ObservedBy(CatalogCacheObserver::class)]
class ServiceHighlight extends Model
{
    use HasTranslations;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order');
    }
}
