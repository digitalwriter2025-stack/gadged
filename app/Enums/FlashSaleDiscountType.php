<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * How a flash sale item price is derived from the regular price.
 */
enum FlashSaleDiscountType: string implements HasLabel
{
    case Percent = 'percent';
    case Amount = 'amount';
    case FixedPrice = 'fixed_price';

    public function getLabel(): string
    {
        return match ($this) {
            self::Percent => 'Percentage off (%)',
            self::Amount => 'Amount off (৳)',
            self::FixedPrice => 'Fixed sale price (৳)',
        };
    }
}
