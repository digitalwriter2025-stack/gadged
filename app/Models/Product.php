<?php

namespace App\Models;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ReviewStatus;
use App\Enums\StockStatus;
use App\Models\Concerns\HasImageConversions;
use App\Models\Concerns\HasTranslations;
use App\Observers\ProductObserver;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Searchable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Product header. Sellable units (price, stock, SKU) are ProductVariant rows; a
 * simple product owns exactly one default variant. min_price, total_stock,
 * rating_avg and similar columns are a read model maintained by
 * ProductAggregateService — never edit them by hand.
 */
#[Fillable([
    'type', 'name', 'name_bn', 'slug', 'sku', 'barcode', 'model', 'brand_id', 'category_id',
    'short_description', 'short_description_bn', 'description', 'description_bn',
    'warranty', 'offer_text', 'faqs', 'condition', 'weight', 'length', 'width', 'height',
    'manage_stock', 'stock_status', 'is_featured', 'is_latest', 'is_best_seller', 'is_trending', 'is_combo', 'flash_sale_eligible',
    'status', 'published_at', 'search_keywords', 'seo_title', 'seo_description', 'canonical_url',
    'created_by',
    'warranty_bn', 'offer_text_bn', 'faqs_bn', 'seo_title_bn', 'seo_description_bn',
])]
#[ObservedBy(ProductObserver::class)]
class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, HasImageConversions, HasTranslations, Searchable, SoftDeletes;

    /**
     * Columns needed to render a product card; use with select() on listings.
     *
     * @var list<string>
     */
    public const CARD_COLUMNS = [
        'id', 'type', 'name', 'name_bn', 'slug', 'brand_id', 'category_id', 'stock_status', 'manage_stock',
        'min_price', 'max_price', 'regular_price', 'discount_percent', 'total_stock',
        'rating_avg', 'rating_count', 'flash_sale_eligible',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ProductType::class,
            'condition' => ProductCondition::class,
            'stock_status' => StockStatus::class,
            'status' => ProductStatus::class,
            'faqs' => 'array',
            'faqs_bn' => 'array',
            'published_at' => 'datetime',
            'manage_stock' => 'boolean',
            'is_featured' => 'boolean',
            'is_latest' => 'boolean',
            'is_best_seller' => 'boolean',
            'is_trending' => 'boolean',
            'is_combo' => 'boolean',
            'flash_sale_eligible' => 'boolean',
            'weight' => 'decimal:3',
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
            'min_price' => 'decimal:2',
            'max_price' => 'decimal:2',
            'regular_price' => 'decimal:2',
            'discount_percent' => 'integer',
            'total_stock' => 'integer',
            'sales_count' => 'integer',
            'view_count' => 'integer',
            'rating_avg' => 'decimal:2',
            'rating_count' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main')->singleFile()->acceptsMimeTypes(self::IMAGE_MIME_TYPES);
        $this->addMediaCollection('gallery')->acceptsMimeTypes(self::IMAGE_MIME_TYPES);
        $this->addMediaCollection('og_image')->singleFile()->acceptsMimeTypes(self::IMAGE_MIME_TYPES);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addWebpConversions(
            collections: ['main', 'gallery'],
            widths: ['thumb' => 160, 'card' => 400, 'medium' => 800, 'large' => 1400],
            responsive: ['large'],
        );

        $this->addMediaConversion('og')
            ->performOnCollections('og_image', 'main')
            ->format('jpg')
            ->fit(Fit::Crop, 1200, 630)
            ->quality(85);
    }

    /**
     * Meilisearch document. Brand and category names are denormalised so
     * "samsung phone" style queries match; the storefront only ever reads
     * relevance-ordered ids back (see ProductFilterService::searchQuery()).
     *
     * @return array{id: int, name: string, name_bn: ?string, sku: ?string, model: ?string, brand: ?string, category: ?string, search_keywords: ?string}
     */
    #[SearchUsingFullText(['name', 'sku', 'model', 'search_keywords'])]
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_bn' => $this->name_bn,
            'sku' => $this->sku,
            'model' => $this->model,
            'brand' => $this->brand?->name,
            'category' => $this->category?->name,
            'search_keywords' => $this->search_keywords,
        ];
    }

    /**
     * Scheduled products are indexed too; the published() scope hides them until
     * published_at passes, so no re-import is needed when they go live.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->status === ProductStatus::Active;
    }

    /**
     * Eager load the relations toSearchableArray() reads during scout:import.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with(['brand:id,name', 'category:id,name']);
    }

    public function isPublished(): bool
    {
        return $this->status === ProductStatus::Active
            && ($this->published_at === null || $this->published_at->isPast());
    }

    public function isVariable(): bool
    {
        return $this->type === ProductType::Variable;
    }

    public function isPurchasable(): bool
    {
        return $this->isPublished()
            && in_array($this->stock_status, [StockStatus::InStock, StockStatus::PreOrder], true);
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Primary category.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Every category the product appears in, including the primary one.
     *
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function activeVariants(): HasMany
    {
        return $this->variants()->where('is_active', true);
    }

    /**
     * @return HasOne<ProductVariant, $this>
     */
    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    /**
     * @return HasMany<ProductSpecificationValue, $this>
     */
    public function specificationValues(): HasMany
    {
        return $this->hasMany(ProductSpecificationValue::class);
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('status', ReviewStatus::Approved);
    }

    /**
     * @return HasMany<FlashSaleProduct, $this>
     */
    public function flashSaleProducts(): HasMany
    {
        return $this->hasMany(FlashSaleProduct::class);
    }

    /**
     * Products bundled inside this combo, in display order.
     *
     * @return HasMany<ComboItem, $this>
     */
    public function comboItems(): HasMany
    {
        return $this->hasMany(ComboItem::class, 'combo_id')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<InventoryTransaction, $this>
     */
    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Visible on the storefront right now.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', ProductStatus::Active)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
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
    protected function trending(Builder $query): void
    {
        $query->where('is_trending', true);
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function combos(Builder $query): void
    {
        $query->where('is_combo', true);
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function inStock(Builder $query): void
    {
        $query->where('stock_status', StockStatus::InStock);
    }

    /**
     * Products assigned to any of the given categories (use Category::subtreeIds()).
     *
     * @param  Builder<self>  $query
     * @param  list<int>  $categoryIds
     */
    #[Scope]
    protected function inCategories(Builder $query, array $categoryIds): void
    {
        $query->whereIn('products.id', fn ($sub) => $sub->select('product_id')
            ->from('category_product')
            ->whereIn('category_id', $categoryIds));
    }

    /**
     * Minimal eager loads for a product card: brand name + main image.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function forCard(Builder $query): void
    {
        $query->select(array_map(fn (string $column): string => "products.{$column}", self::CARD_COLUMNS))
            ->with(['brand:id,name,slug', 'media' => fn ($media) => $media->where('collection_name', 'main')]);
    }
}
