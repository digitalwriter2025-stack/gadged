<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Observers\CatalogCacheObserver;
use Carbon\CarbonInterface;
use Database\Factories\FlashSaleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Time-boxed campaign. "Running" is always decided with server time.
 */
#[Fillable(['title', 'slug', 'description', 'starts_at', 'ends_at', 'is_active', 'sort_order', 'title_bn', 'description_bn'])]
#[ObservedBy(CatalogCacheObserver::class)]
class FlashSale extends Model
{
    /** @use HasFactory<FlashSaleFactory> */
    use HasFactory, HasTranslations;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function isRunning(?CarbonInterface $at = null): bool
    {
        $at ??= now();

        return $this->is_active && $this->starts_at->lte($at) && $this->ends_at->gt($at);
    }

    /**
     * @return HasMany<FlashSaleProduct, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(FlashSaleProduct::class)->orderBy('sort_order');
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function running(Builder $query, ?CarbonInterface $at = null): void
    {
        $at ??= now();

        $query->where('is_active', true)
            ->where('starts_at', '<=', $at)
            ->where('ends_at', '>', $at);
    }
}
