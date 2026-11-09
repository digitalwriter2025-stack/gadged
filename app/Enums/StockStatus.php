<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Availability shown to customers.
 */
enum StockStatus: string implements HasColor, HasLabel
{
    case InStock = 'in_stock';
    case OutOfStock = 'out_of_stock';
    case PreOrder = 'pre_order';
    case Upcoming = 'upcoming';

    /**
     * In the storefront language; the admin panel shows English.
     */
    public function getLabel(): string
    {
        return __(match ($this) {
            self::InStock => 'In Stock',
            self::OutOfStock => 'Out of Stock',
            self::PreOrder => 'Pre Order',
            self::Upcoming => 'Upcoming',
        });
    }

    public function getColor(): string
    {
        return match ($this) {
            self::InStock => 'success',
            self::OutOfStock => 'danger',
            self::PreOrder => 'info',
            self::Upcoming => 'warning',
        };
    }
}
