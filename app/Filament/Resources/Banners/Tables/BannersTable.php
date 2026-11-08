<?php

declare(strict_types=1);

namespace App\Filament\Resources\Banners\Tables;

use App\Enums\BannerPlacement;
use App\Models\Banner;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BannersTable
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

                ImageColumn::make('image')
                    ->label('Preview')
                    ->state(fn (Banner $record): ?string => $record->getFirstMediaUrl('desktop'))
                    ->width('120px')
                    ->height('50px'),

                TextColumn::make('title')
                    ->label('Title / Alt')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (Banner $record): ?string => $record->subtitle ?? $record->url),

                TextColumn::make('placement')
                    ->label('Placement')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof BannerPlacement ? $state->getLabel() : (string) $state)
                    ->color(fn ($state): string => match ($state instanceof BannerPlacement ? $state : BannerPlacement::tryFrom((string) $state)) {
                        BannerPlacement::HeroMain => 'primary',
                        BannerPlacement::HeroSideTop, BannerPlacement::HeroSideBottom => 'info',
                        BannerPlacement::Promo => 'warning',
                        default => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ends_at')
                    ->label('Ends')
                    ->dateTime('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('placement')
                    ->options(BannerPlacement::class),
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
