<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * How the customer pays for an order.
 */
enum PaymentMethod: string implements HasColor, HasLabel
{
    case CashOnDelivery = 'cod';
    case Bkash = 'bkash';
    case Nagad = 'nagad';
    case BankTransfer = 'bank_transfer';
    case Online = 'online';

    /**
     * In the storefront language; the admin panel shows English.
     */
    public function getLabel(): string
    {
        return __(match ($this) {
            self::CashOnDelivery => 'Cash on Delivery',
            self::Bkash => 'bKash',
            self::Nagad => 'Nagad',
            self::BankTransfer => 'Bank Transfer',
            self::Online => 'Online Payment',
        });
    }

    public function getColor(): string
    {
        return match ($this) {
            self::CashOnDelivery => 'gray',
            self::Bkash => 'danger',
            self::Nagad => 'warning',
            self::BankTransfer => 'info',
            self::Online => 'success',
        };
    }
}
