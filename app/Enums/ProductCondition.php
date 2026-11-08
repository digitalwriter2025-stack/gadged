<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Physical condition of a product.
 */
enum ProductCondition: string implements HasLabel
{
    case New = 'new';
    case Used = 'used';
    case Refurbished = 'refurbished';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Used => 'Used',
            self::Refurbished => 'Refurbished',
        };
    }
}
