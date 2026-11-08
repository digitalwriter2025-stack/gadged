<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['category', 'brand', 'media']))
            ->columns([
                ImageColumn::make('image')
                    ->label('Image')
                    ->state(fn (Product $record): ?string => $record->getFirstMediaUrl('main'))
                    ->square()
                    ->size(50),

                TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (Product $record): string => 'SKU: '.$record->sku.($record->model ? ' | Model: '.$record->model : '')),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('brand.name')
                    ->label('Brand')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('min_price')
                    ->label('Price')
                    ->money('BDT')
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('total_stock')
                    ->label('Stock')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 5 => 'warning',
                        default => 'success',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),
                SelectFilter::make('brand_id')
                    ->relationship('brand', 'name')
                    ->label('Brand'),
                TernaryFilter::make('is_featured')
                    ->label('Featured Only'),
                SelectFilter::make('status')
                    ->options([
                        'published' => 'Published',
                        'draft' => 'Draft',
                    ]),
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
