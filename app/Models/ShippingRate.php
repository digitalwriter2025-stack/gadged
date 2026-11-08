<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Charge for delivering to a zone with a method; editable from the admin panel.
 */
#[Fillable([
    'shipping_zone_id', 'shipping_method_id', 'charge', 'free_shipping_threshold',
    'min_delivery_days', 'max_delivery_days', 'is_active',
])]
class ShippingRate extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'charge' => 'decimal:2',
            'free_shipping_threshold' => 'decimal:2',
            'min_delivery_days' => 'integer',
            'max_delivery_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Charge for a cart subtotal, applying the free-shipping threshold.
     */
    public function chargeFor(string $subtotal): string
    {
        if ($this->free_shipping_threshold !== null && Money::compare($subtotal, $this->free_shipping_threshold) >= 0) {
            return '0.00';
        }

        return Money::of($this->charge);
    }

    /**
     * @return BelongsTo<ShippingZone, $this>
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    /**
     * @return BelongsTo<ShippingMethod, $this>
     */
    public function method(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }
}
