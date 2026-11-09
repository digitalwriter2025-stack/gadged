<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Support\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Delivery option the customer picks at checkout, such as "Inside Dhaka" or
 * "Express (Dhaka)", with its own charge; managed from the admin panel.
 */
#[Fillable([
    'name', 'code', 'description', 'charge', 'free_shipping_threshold', 'delivery_estimate', 'is_active', 'sort_order',
    'name_bn', 'description_bn', 'delivery_estimate_bn',
])]
class ShippingMethod extends Model
{
    use HasTranslations;

    protected static function booted(): void
    {
        static::creating(function (ShippingMethod $method): void {
            if (blank($method->code)) {
                $method->code = static::uniqueCodeFor($method->name);
            }

            if ($method->sort_order === null) {
                $method->sort_order = (int) static::query()->max('sort_order') + 1;
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'charge' => 'decimal:2',
            'free_shipping_threshold' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
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
     * Snake-case code derived from the name, suffixed when already taken.
     */
    public static function uniqueCodeFor(string $name): string
    {
        $base = Str::limit(Str::slug($name, '_'), 40, '') ?: 'method';
        $code = $base;
        $suffix = 2;

        while (static::query()->where('code', $code)->exists()) {
            $code = "{$base}_{$suffix}";
            $suffix++;
        }

        return $code;
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }
}
