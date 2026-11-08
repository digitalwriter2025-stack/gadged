<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServiceHighlights\Tables;

use App\Models\ServiceHighlight;
use App\Support\Icons\IconHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ServiceHighlightsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width('60px'),

                TextColumn::make('icon')
                    ->label('Icon')
                    ->formatStateUsing(fn (string $state): HtmlString => new HtmlString(
                        '<div class="flex items-center gap-2">'.IconHelper::render($state, 'size-5 text-orange-500').' <span class="text-xs text-gray-500">('.e($state).')</span></div>'
                    )),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (ServiceHighlight $record): ?string => $record->subtitle),

                TextColumn::make('url')
                    ->label('Link')
                    ->toggleable(isToggledHiddenByDefault: true),

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
