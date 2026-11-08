<?php

namespace App\Models;

use App\Enums\HomeSectionType;
use App\Models\Concerns\HasImageConversions;
use App\Observers\CatalogCacheObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Ordered homepage block. `settings` carries type-specific options such as
 * {"source": "latest", "limit": 12} for a product collection. Showcase
 * sections keep their collage artwork in the "images" media collection.
 */
#[Fillable(['type', 'title', 'subtitle', 'settings', 'is_active', 'sort_order'])]
#[ObservedBy(CatalogCacheObserver::class)]
class HomeSection extends Model implements HasMedia
{
    use HasImageConversions;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->acceptsMimeTypes(self::IMAGE_MIME_TYPES);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addWebpConversions(['images'], ['card' => 400]);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => HomeSectionType::class,
            'settings' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * @return HasMany<HomeSectionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(HomeSectionItem::class)->orderBy('sort_order');
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
