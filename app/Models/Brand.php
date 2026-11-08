<?php

namespace App\Models;

use App\Models\Concerns\HasImageConversions;
use App\Observers\CatalogCacheObserver;
use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable([
    'name', 'slug', 'description', 'website', 'is_active', 'is_featured', 'sort_order',
    'seo_title', 'seo_description',
])]
#[ObservedBy(CatalogCacheObserver::class)]
class Brand extends Model implements HasMedia
{
    /** @use HasFactory<BrandFactory> */
    use HasFactory, HasImageConversions, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile()->acceptsMimeTypes(self::IMAGE_MIME_TYPES);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addWebpConversions(['logo'], ['thumb' => 160, 'card' => 320]);
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }
}
