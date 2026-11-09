<?php

declare(strict_types=1);

namespace App\Filament\Resources\LedgerEntries\Schemas;

use App\Enums\LedgerAccount;
use App\Enums\LedgerCategory;
use App\Enums\LedgerEntryType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class LedgerEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Income / expense entry')
                    ->description('Record money that did not come from a website order: shop rent, salaries, ads, courier bills, stock purchases or service income. Order sales are counted automatically.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        // The model derives the stored type from the category; this only filters the categories.
                        ToggleButtons::make('type')
                            ->label('Type')
                            ->options(LedgerEntryType::class)
                            ->default(LedgerEntryType::Expense->value)
                            ->inline()
                            ->required()
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(fn (Set $set) => $set('category', null))
                            ->columnSpanFull(),

                        Select::make('category')
                            ->label('Category')
                            ->options(fn (Get $get): array => LedgerCategory::options(self::selectedType($get)))
                            ->required()
                            ->native(false)
                            ->live()
                            ->helperText(fn (Get $get): ?string => self::categoryHint($get('category'))),

                        TextInput::make('title')
                            ->label('Description')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('e.g. November shop rent'),

                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->maxValue(9999999999.99)
                            ->prefix('৳'),

                        DatePicker::make('entry_date')
                            ->label('Date')
                            ->required()
                            ->native(false)
                            ->default(fn (): string => now(config('app.display_timezone'))->toDateString()),

                        Select::make('account')
                            ->label('Paid / received via')
                            ->options(LedgerAccount::class)
                            ->default(LedgerAccount::Cash->value)
                            ->required()
                            ->native(false),

                        TextInput::make('reference')
                            ->label('Reference')
                            ->maxLength(100)
                            ->placeholder('Invoice no., bKash TrxID, ...'),

                        Textarea::make('note')
                            ->label('Note')
                            ->rows(3)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * Explains why stock purchases and owner capital do not change the profit figure.
     */
    private static function categoryHint(mixed $state): ?string
    {
        $category = $state instanceof LedgerCategory ? $state : LedgerCategory::tryFrom((string) $state);

        return match ($category) {
            LedgerCategory::StockPurchase => 'Counted as money spent, but not deducted from profit: profit already subtracts the cost price of the products sold.',
            LedgerCategory::Investment, LedgerCategory::OwnerWithdrawal => 'Owner capital, shown separately from profit.',
            default => null,
        };
    }

    private static function selectedType(Get $get): LedgerEntryType
    {
        $type = $get('type');

        return $type instanceof LedgerEntryType ? $type : (LedgerEntryType::tryFrom((string) $type) ?? LedgerEntryType::Expense);
    }
}
