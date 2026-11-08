<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * How a variation attribute is presented to customers.
 */
enum AttributeDisplayType: string implements HasLabel
{
    case Select = 'select';
    case Button = 'button';
    case Swatch = 'swatch';

    public function getLabel(): string
    {
        return match ($this) {
            self::Select => 'Dropdown',
            self::Button => 'Buttons',
            self::Swatch => 'Colour swatches',
        };
    }
}
