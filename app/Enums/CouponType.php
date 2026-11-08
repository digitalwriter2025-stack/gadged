<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * How a coupon discount is calculated.
 */
enum CouponType: string implements HasLabel
{
    case Fixed = 'fixed';
    case Percent = 'percent';

    public function getLabel(): string
    {
        return match ($this) {
            self::Fixed => 'Fixed amount',
            self::Percent => 'Percentage',
        };
    }
}
