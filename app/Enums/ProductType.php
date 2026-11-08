<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Whether a product has selectable variations.
 */
enum ProductType: string implements HasLabel
{
    case Simple = 'simple';
    case Variable = 'variable';

    public function getLabel(): string
    {
        return match ($this) {
            self::Simple => 'Simple',
            self::Variable => 'Variable',
        };
    }
}
