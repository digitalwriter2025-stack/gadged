<?php

declare(strict_types=1);

namespace App\Filament\Resources\HomeSections\Tables;

use App\Enums\HomeSectionType;
use App\Models\HomeSection;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HomeSectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width('60px'),

                TextColumn::make('title')
                    ->label('Section Title')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (HomeSection $record): ?string => $record->subtitle),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof HomeSectionType ? $state->getLabel() : (string) $state)
                    ->color(fn ($state): string => match ($state instanceof HomeSectionType ? $state : HomeSectionType::tryFrom((string) $state)) {
                        HomeSectionType::Hero => 'primary',
                        HomeSectionType::FlashSale => 'danger',
                        HomeSectionType::Showcase => 'purple',
                        HomeSectionType::ProductCollection => 'info',
                        HomeSectionType::FeaturedCategories => 'success',
                        HomeSectionType::FeaturedBrands => 'warning',
                        default => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y, h:i A')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('type')
                    ->options(HomeSectionType::class),
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
