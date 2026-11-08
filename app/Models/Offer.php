<?php

namespace App\Models;

use App\Models\Concerns\HasImageConversions;
use App\Observers\CatalogCacheObserver;
use Carbon\CarbonInterface;
use Database\Factories\OfferFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Promotional campaign listed on the Offers page (and the Mobile Deal page
 * when flagged), with a square poster and the products it covers.
 */
#[Fillable([
    'title', 'slug', 'summary', 'description', 'availability', 'is_mobile_deal',
    'starts_at', 'ends_at', 'is_active', 'sort_order',
])]
#[ObservedBy(CatalogCacheObserver::class)]
class Offer extends Model implements HasMedia
{
    /** @use HasFactory<OfferFactory> */
    use HasFactory, HasImageConversions;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_mobile_deal' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('poster')->singleFile()->acceptsMimeTypes(self::IMAGE_MIME_TYPES);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addWebpConversions(['poster'], ['card' => 480, 'large' => 900]);
    }

    /**
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function isRunning(?CarbonInterface $at = null): bool
    {
        $at ??= now();

        return $this->is_active && $this->starts_at->lte($at) && $this->ends_at->gt($at);
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
            ->where('ends_at', '>', $at)
            ->orderBy('sort_order')
            ->latest('starts_at');
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function mobileDeals(Builder $query): void
    {
        $query->where('is_mobile_deal', true);
    }
}
