<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Reason a stock level changed. The sign of the quantity is decided by the type.
 */
enum InventoryTransactionType: string implements HasColor, HasLabel
{
    case StockIn = 'stock_in';
    case StockOut = 'stock_out';
    case Order = 'order';
    case OrderCancellation = 'order_cancellation';
    case Return = 'return';
    case Damage = 'damage';
    case Adjustment = 'adjustment';

    /**
     * +1 adds stock, -1 removes stock, 0 means the caller supplies a signed quantity.
     */
    public function direction(): int
    {
        return match ($this) {
            self::StockIn, self::OrderCancellation, self::Return => 1,
            self::StockOut, self::Order, self::Damage => -1,
            self::Adjustment => 0,
        };
    }

    /**
     * Types a staff member may record by hand from the admin panel.
     *
     * @return list<self>
     */
    public static function manual(): array
    {
        return [self::StockIn, self::StockOut, self::Return, self::Damage, self::Adjustment];
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::StockIn => 'Stock in',
            self::StockOut => 'Stock out',
            self::Order => 'Order',
            self::OrderCancellation => 'Order cancellation',
            self::Return => 'Return',
            self::Damage => 'Damage',
            self::Adjustment => 'Manual adjustment',
        };
    }

    public function getColor(): string
    {
        return match ($this->direction()) {
            1 => 'success',
            -1 => 'danger',
            default => 'warning',
        };
    }
}
