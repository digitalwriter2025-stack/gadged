<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Where the money of a ledger entry was paid from or received into.
 */
enum LedgerAccount: string implements HasLabel
{
    case Cash = 'cash';
    case Bkash = 'bkash';
    case Nagad = 'nagad';
    case Rocket = 'rocket';
    case Bank = 'bank';

    public function getLabel(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Bkash => 'bKash',
            self::Nagad => 'Nagad',
            self::Rocket => 'Rocket',
            self::Bank => 'Bank',
        };
    }
}
