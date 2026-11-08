<?php

namespace App\Models;

use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Placed order with a complete snapshot of customer, delivery and pricing data.
 *
 * status / payment_status are not fillable: they change only through the order
 * services so each change is validated and written to order_status_histories.
 */
#[Fillable([
    'order_number', 'user_id', 'payment_method', 'source',
    'customer_name', 'customer_phone', 'customer_email',
    'shipping_address', 'shipping_area', 'district_id', 'shipping_district',
    'shipping_zone_id', 'shipping_zone_name', 'shipping_method_id', 'shipping_method_name',
    'customer_note', 'subtotal', 'coupon_id', 'coupon_code', 'coupon_discount', 'manual_discount',
    'discount_total', 'shipping_total', 'tax_total', 'grand_total', 'currency',
    'courier_name', 'tracking_number', 'payment_due_at', 'created_by', 'ip_address', 'user_agent',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'payment_method' => PaymentMethod::class,
            'source' => OrderSource::class,
            'subtotal' => 'decimal:2',
            'coupon_discount' => 'decimal:2',
            'manual_discount' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'shipping_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'paid_total' => 'decimal:2',
            'refunded_total' => 'decimal:2',
            'payment_due_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<OrderStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at')->orderBy('id');
    }

    /**
     * @return HasMany<OrderNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(OrderNote::class)->latest();
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasMany<Refund, $this>
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * @return HasOne<CouponUsage, $this>
     */
    public function couponUsage(): HasOne
    {
        return $this->hasOne(CouponUsage::class);
    }

    /**
     * @return BelongsTo<Coupon, $this>
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class)->withTrashed();
    }

    /**
     * @return BelongsTo<District, $this>
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * @return BelongsTo<ShippingZone, $this>
     */
    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }

    /**
     * @return BelongsTo<ShippingMethod, $this>
     */
    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function withStatus(Builder $query, OrderStatus ...$statuses): void
    {
        $query->whereIn('status', $statuses);
    }

    /**
     * Orders that count towards revenue.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function revenue(Builder $query): void
    {
        $query->whereIn('status', OrderStatus::revenueStatuses());
    }
}
