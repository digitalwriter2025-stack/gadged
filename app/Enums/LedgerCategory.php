<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * What a ledger entry was for. Each category belongs to exactly one entry type.
 */
enum LedgerCategory: string implements HasLabel
{
    case ServiceIncome = 'service_income';
    case Investment = 'investment';
    case OtherIncome = 'other_income';

    case StockPurchase = 'stock_purchase';
    case Rent = 'rent';
    case Salary = 'salary';
    case Utilities = 'utilities';
    case Marketing = 'marketing';
    case Courier = 'courier';
    case Packaging = 'packaging';
    case Transport = 'transport';
    case Maintenance = 'maintenance';
    case OwnerWithdrawal = 'owner_withdrawal';
    case OtherExpense = 'other_expense';

    public function type(): LedgerEntryType
    {
        return match ($this) {
            self::ServiceIncome, self::Investment, self::OtherIncome => LedgerEntryType::Income,
            default => LedgerEntryType::Expense,
        };
    }

    /**
     * Whether the entry belongs on the profit & loss statement.
     *
     * Owner investment and withdrawals move capital, not profit. Stock purchases
     * are left out because profit already deducts the cost price of the items sold.
     */
    public function affectsProfit(): bool
    {
        return ! in_array($this, [self::Investment, self::StockPurchase, self::OwnerWithdrawal], true);
    }

    /**
     * @return list<self>
     */
    public static function forType(LedgerEntryType $type): array
    {
        return array_values(array_filter(self::cases(), fn (self $category): bool => $category->type() === $type));
    }

    /**
     * @return array<string, string>
     */
    public static function options(?LedgerEntryType $type = null): array
    {
        $categories = $type === null ? self::cases() : self::forType($type);

        return collect($categories)
            ->mapWithKeys(fn (self $category): array => [$category->value => $category->getLabel()])
            ->all();
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::ServiceIncome => 'Service / repair income',
            self::Investment => 'Owner investment',
            self::OtherIncome => 'Other income',
            self::StockPurchase => 'Stock purchase',
            self::Rent => 'Shop rent',
            self::Salary => 'Staff salary',
            self::Utilities => 'Utilities (electricity, internet)',
            self::Marketing => 'Marketing & ads',
            self::Courier => 'Courier & delivery',
            self::Packaging => 'Packaging',
            self::Transport => 'Transport',
            self::Maintenance => 'Repairs & maintenance',
            self::OwnerWithdrawal => 'Owner withdrawal',
            self::OtherExpense => 'Other expense',
        };
    }
}
