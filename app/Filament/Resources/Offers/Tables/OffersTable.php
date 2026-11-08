<?php

declare(strict_types=1);

namespace App\Filament\Resources\Offers\Tables;

use App\Models\Offer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OffersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['media']))
            ->columns([
                ImageColumn::make('poster')
                    ->label('Artwork')
                    ->state(fn (Offer $record): ?string => $record->getFirstMediaUrl('poster'))
                    ->square(),

                TextColumn::make('title')
                    ->label('Offer Title')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (Offer $record): ?string => $record->summary),

                TextColumn::make('availability')
                    ->label('Availability')
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_mobile_deal')
                    ->label('Mobile Deal')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('starts_at')
                    ->label('Valid From')
                    ->dateTime('d M Y')
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label('Valid Till')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('starts_at', 'desc')
            ->filters([
                TernaryFilter::make('is_mobile_deal')
                    ->label('Mobile Deals Only'),
                TernaryFilter::make('is_active')
                    ->label('Active Only'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
