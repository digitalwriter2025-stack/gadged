<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Which cart lines a coupon applies to.
 */
enum CouponScope: string implements HasLabel
{
    case All = 'all';
    case Products = 'products';
    case Categories = 'categories';

    public function getLabel(): string
    {
        return match ($this) {
            self::All => 'Entire cart',
            self::Products => 'Selected products',
            self::Categories => 'Selected categories',
        };
    }
}
