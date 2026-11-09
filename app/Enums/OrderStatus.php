<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Fulfilment lifecycle of an order. Allowed moves are defined once in transitions().
 */
enum OrderStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Packed = 'packed';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Returned = 'returned';
    case Refunded = 'refunded';

    /**
     * @return list<self>
     */
    public function transitions(): array
    {
        return match ($this) {
            self::Pending => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Processing, self::Cancelled],
            self::Processing => [self::Packed, self::Cancelled],
            self::Packed => [self::Shipped, self::Cancelled],
            self::Shipped => [self::Delivered, self::Returned],
            self::Delivered => [self::Returned],
            self::Returned => [self::Refunded],
            self::Cancelled, self::Refunded => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->transitions(), true);
    }

    /**
     * Whether moving into this status puts reserved stock back on the shelf.
     */
    public function restoresStock(): bool
    {
        return in_array($this, [self::Cancelled, self::Returned], true);
    }

    public function isFinal(): bool
    {
        return $this->transitions() === [];
    }

    /**
     * Statuses that count as revenue on reports.
     *
     * @return list<self>
     */
    public static function revenueStatuses(): array
    {
        return [self::Confirmed, self::Processing, self::Packed, self::Shipped, self::Delivered];
    }

    /**
     * Name of the order timestamp column set when entering this status.
     */
    public function timestampColumn(): ?string
    {
        return match ($this) {
            self::Confirmed => 'confirmed_at',
            self::Shipped => 'shipped_at',
            self::Delivered => 'delivered_at',
            self::Cancelled => 'cancelled_at',
            default => null,
        };
    }

    /**
     * In the storefront language; the admin panel shows English.
     */
    public function getLabel(): string
    {
        return __(match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Processing => 'Processing',
            self::Packed => 'Packed',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
            self::Returned => 'Returned',
            self::Refunded => 'Refunded',
        });
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Confirmed, self::Processing, self::Packed => 'info',
            self::Shipped => 'primary',
            self::Delivered => 'success',
            self::Cancelled, self::Returned => 'danger',
            self::Refunded => 'gray',
        };
    }
}
