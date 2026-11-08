<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Channel an order was placed through.
 */
enum OrderSource: string implements HasColor, HasLabel
{
    case Web = 'web';
    case Admin = 'admin';

    public function getLabel(): string
    {
        return match ($this) {
            self::Web => 'Website',
            self::Admin => 'Manual (admin)',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Web => 'info',
            self::Admin => 'gray',
        };
    }
}
