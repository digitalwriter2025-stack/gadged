<?php

declare(strict_types=1);

namespace App\Filament\Resources\Brands\Tables;

use App\Models\Brand;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BrandsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['media']))
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width('50px'),

                ImageColumn::make('logo')
                    ->label('Logo')
                    ->state(fn (Brand $record): ?string => $record->getFirstMediaUrl('logo'))
                    ->circular(),

                TextColumn::make('name')
                    ->label('Brand Name')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('website')
                    ->label('Website')
                    ->url(fn (Brand $record): ?string => $record->website, shouldOpenInNewTab: true)
                    ->color('primary')
                    ->toggleable(),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
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
