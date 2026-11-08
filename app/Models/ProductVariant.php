<?php

namespace App\Models;

use App\Models\Concerns\HasImageConversions;
use App\Observers\ProductVariantObserver;
use App\Support\Money;
use Carbon\CarbonInterface;
use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
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
 * Sellable unit: SKU, price and stock.
 *
 * stock_quantity is intentionally NOT fillable — it may only change through
 * InventoryService so that every change is locked and written to the ledger.
 */
#[Fillable([
    'product_id', 'sku', 'barcode', 'name', 'combination_key', 'price', 'sale_price',
    'sale_starts_at', 'sale_ends_at', 'cost_price', 'low_stock_threshold', 'weight',
    'is_default', 'is_active', 'sort_order',
])]
#[Hidden(['cost_price'])]
#[ObservedBy(ProductVariantObserver::class)]
class ProductVariant extends Model implements HasMedia
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory, HasImageConversions, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'sale_starts_at' => 'datetime',
            'sale_ends_at' => 'datetime',
            'stock_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'weight' => 'decimal:3',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile()->acceptsMimeTypes(self::IMAGE_MIME_TYPES);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addWebpConversions(['image'], ['thumb' => 160, 'card' => 400, 'large' => 1400]);
    }

    /**
     * Whether the variant's own sale price applies at the given moment
     * (flash sales are resolved separately by the pricing service).
     */
    public function isOnSale(?CarbonInterface $at = null): bool
    {
        $at ??= now();

        if ($this->sale_price === null || Money::compare($this->sale_price, $this->price) >= 0) {
            return false;
        }

        return ($this->sale_starts_at === null || $this->sale_starts_at->lte($at))
            && ($this->sale_ends_at === null || $this->sale_ends_at->gt($at));
    }

    /**
     * Regular selling price, honouring the scheduled sale window.
     */
    public function currentPrice(?CarbonInterface $at = null): string
    {
        return $this->isOnSale($at) ? Money::of($this->sale_price) : Money::of($this->price);
    }

    public function lowStockThreshold(): int
    {
        return $this->low_stock_threshold ?? (int) config('shop.inventory.low_stock_threshold');
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->lowStockThreshold();
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsToMany<AttributeValue, $this>
     */
    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class);
    }

    /**
     * @return HasMany<InventoryTransaction, $this>
     */
    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
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
     * Variants at or below their low-stock threshold (falls back to the global setting).
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function lowStock(Builder $query): void
    {
        $query->whereRaw(
            'stock_quantity <= COALESCE(low_stock_threshold, ?)',
            [(int) config('shop.inventory.low_stock_threshold')],
        );
    }
}
