<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * How a flash sale item price is derived from the regular price.
 */
enum FlashSaleDiscountType: string implements HasLabel
{
    case FixedPrice = 'fixed_price';
    case Amount = 'amount';
    case Percent = 'percent';

    public function getLabel(): string
    {
        return match ($this) {
            self::FixedPrice => 'Fixed sale price',
            self::Amount => 'Amount off',
            self::Percent => 'Percentage off',
        };
    }
}
