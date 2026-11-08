<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
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

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['parent', 'media']))
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width('50px'),

                ImageColumn::make('image')
                    ->label('Thumb')
                    ->state(fn (Category $record): ?string => $record->getFirstMediaUrl('image'))
                    ->circular(),

                TextColumn::make('name')
                    ->label('Category Name')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (Category $record): ?string => $record->parent?->name ? 'Parent: '.$record->parent->name : null),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->badge()
                    ->color('gray')
                    ->url(fn (Category $record): string => route('store.categories.show', $record->slug), shouldOpenInNewTab: true),

                IconColumn::make('show_in_menu')
                    ->label('In Header')
                    ->boolean()
                    ->sortable(),

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
            ->filters([
                TernaryFilter::make('show_in_menu')
                    ->label('Header Menu Only'),
                TernaryFilter::make('is_featured')
                    ->label('Featured Categories Only'),
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
