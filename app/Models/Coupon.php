<?php

namespace App\Models;

use App\Enums\CouponScope;
use App\Enums\CouponType;
use Carbon\CarbonInterface;
use Database\Factories\CouponFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute as AttributeCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Discount code. Full rule evaluation (subtotal, per-customer limit, scope) is done
 * server-side by the coupon validator at cart/checkout time.
 */
#[Fillable([
    'code', 'description', 'type', 'value', 'min_subtotal', 'max_discount', 'usage_limit',
    'usage_limit_per_customer', 'scope', 'starts_at', 'ends_at', 'is_active',
])]
class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'scope' => CouponScope::class,
            'value' => 'decimal:2',
            'min_subtotal' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'usage_limit' => 'integer',
            'usage_limit_per_customer' => 'integer',
            'used_count' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Codes are matched case-insensitively and stored upper-case.
     */
    protected function code(): AttributeCast
    {
        return AttributeCast::make(set: fn (string $value): string => mb_strtoupper(trim($value)));
    }

    public function isWithinSchedule(?CarbonInterface $at = null): bool
    {
        $at ??= now();

        return ($this->starts_at === null || $this->starts_at->lte($at))
            && ($this->ends_at === null || $this->ends_at->gt($at));
    }

    public function hasUsesLeft(): bool
    {
        return $this->usage_limit === null || $this->used_count < $this->usage_limit;
    }

    /**
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * @return HasMany<CouponUsage, $this>
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }
}
