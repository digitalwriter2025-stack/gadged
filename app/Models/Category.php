<?php

namespace App\Models;

use App\Models\Concerns\HasImageConversions;
use App\Observers\CategoryObserver;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Hierarchical category. `path` holds the ancestor chain including itself
 * ("/3/12/") and is maintained by CategoryObserver, so subtrees and breadcrumbs
 * are single queries instead of recursion.
 */
#[Fillable([
    'parent_id', 'name', 'name_bn', 'slug', 'description', 'icon', 'sort_order',
    'is_active', 'is_featured', 'show_in_menu', 'seo_title', 'seo_description',
])]
#[ObservedBy(CategoryObserver::class)]
class Category extends Model implements HasMedia
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, HasImageConversions, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'depth' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'show_in_menu' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile()->acceptsMimeTypes(self::IMAGE_MIME_TYPES);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addWebpConversions(['image'], ['thumb' => 160, 'card' => 400]);
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<self, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Products whose primary category is this one.
     *
     * @return HasMany<Product, $this>
     */
    public function primaryProducts(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * All products assigned to this category (primary + additional).
     *
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    /**
     * Specifications used (and optionally filterable) for products in this category.
     *
     * @return BelongsToMany<Specification, $this>
     */
    public function specifications(): BelongsToMany
    {
        return $this->belongsToMany(Specification::class)
            ->withPivot(['is_filterable', 'sort_order'])
            ->orderByPivot('sort_order');
    }

    /**
     * @return list<int>
     */
    public function ancestorIds(): array
    {
        $ids = array_map('intval', array_filter(explode('/', (string) $this->path)));

        return array_values(array_diff($ids, [$this->id]));
    }

    /**
     * Ids of this category and every descendant, via one indexed prefix query.
     *
     * @return list<int>
     */
    public function subtreeIds(): array
    {
        return self::query()
            ->where('path', 'like', $this->path.'%')
            ->pluck('id')
            ->all();
    }

    public function isAncestorOf(self $category): bool
    {
        return $category->id !== $this->id && str_starts_with((string) $category->path, (string) $this->path);
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
    protected function roots(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }
}
