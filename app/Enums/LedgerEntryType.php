<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Direction of a manually recorded ledger entry: money in or money out.
 */
enum LedgerEntryType: string implements HasColor, HasIcon, HasLabel
{
    case Income = 'income';
    case Expense = 'expense';

    public function getLabel(): string
    {
        return match ($this) {
            self::Income => 'Income',
            self::Expense => 'Expense',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Income => 'success',
            self::Expense => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Income => 'heroicon-m-arrow-down-left',
            self::Expense => 'heroicon-m-arrow-up-right',
        };
    }
}
