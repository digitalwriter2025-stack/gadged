<?php

declare(strict_types=1);

namespace App\Filament\Resources\LedgerEntries;

use App\Filament\Resources\LedgerEntries\Pages\CreateLedgerEntry;
use App\Filament\Resources\LedgerEntries\Pages\EditLedgerEntry;
use App\Filament\Resources\LedgerEntries\Pages\ListLedgerEntries;
use App\Filament\Resources\LedgerEntries\Schemas\LedgerEntryForm;
use App\Filament\Resources\LedgerEntries\Tables\LedgerEntriesTable;
use App\Models\LedgerEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

/**
 * Income and expenses recorded by hand (rent, salaries, ads, service income, ...).
 * Order revenue is not entered here; the finance report reads it from orders.
 */
class LedgerEntryResource extends Resource
{
    protected static ?string $model = LedgerEntry::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Accounts';

    protected static ?string $navigationLabel = 'Income & Expenses';

    protected static ?string $modelLabel = 'entry';

    protected static ?string $pluralModelLabel = 'income & expenses';

    protected static ?string $slug = 'income-expenses';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return LedgerEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LedgerEntriesTable::configure($table);
    }

    /**
     * @return list<string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'reference'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLedgerEntries::route('/'),
            'create' => CreateLedgerEntry::route('/create'),
            'edit' => EditLedgerEntry::route('/{record}/edit'),
        ];
    }
}
