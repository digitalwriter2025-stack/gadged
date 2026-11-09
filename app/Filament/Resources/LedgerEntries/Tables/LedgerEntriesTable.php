<?php

declare(strict_types=1);

namespace App\Filament\Resources\LedgerEntries\Tables;

use App\Enums\LedgerAccount;
use App\Enums\LedgerCategory;
use App\Enums\LedgerEntryType;
use App\Models\LedgerEntry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class LedgerEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('entry_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Description')
                    ->searchable(['title', 'reference', 'note'])
                    ->weight('medium')
                    ->description(fn (LedgerEntry $record): ?string => $record->reference),

                TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->color(fn (LedgerEntry $record): string => $record->type->getColor())
                    ->icon(fn (LedgerEntry $record): string => $record->type->getIcon()),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('BDT')
                    ->sortable()
                    ->weight('bold')
                    ->color(fn (LedgerEntry $record): string => $record->type->getColor())
                    ->summarize([
                        self::totalOf(LedgerEntryType::Income),
                        self::totalOf(LedgerEntryType::Expense),
                    ]),

                TextColumn::make('account')
                    ->label('Via')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('recorder.name')
                    ->label('Recorded by')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Added')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('entry_date', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->label('Category')
                    ->options(LedgerCategory::options())
                    ->multiple(),
                SelectFilter::make('account')
                    ->label('Paid / received via')
                    ->options(LedgerAccount::class),
                Filter::make('entry_date')
                    ->label('Date')
                    ->schema([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('entry_date', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date) => $query->whereDate('entry_date', '<=', $date)))
                    ->indicateUsing(fn (array $data): array => array_filter([
                        'from' => ($data['from'] ?? null) ? 'From '.$data['from'] : null,
                        'until' => ($data['until'] ?? null) ? 'Until '.$data['until'] : null,
                    ])),
            ])
            ->filtersFormColumns(2)
            ->recordActions([
                EditAction::make()->iconButton()->tooltip('Edit'),
                DeleteAction::make()->iconButton()->tooltip('Delete'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Footer total of one entry type across the filtered rows.
     */
    private static function totalOf(LedgerEntryType $type): Summarizer
    {
        return Summarizer::make($type->value)
            ->label("Total {$type->getLabel()}")
            ->money('BDT')
            ->using(fn (QueryBuilder $query): float => (float) $query->where('type', $type->value)->sum('amount'));
    }
}
