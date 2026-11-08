<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Tables;

use App\Models\Page;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Page Title')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (Page $record): string => '/page/'.$record->slug),

                TextColumn::make('slug')
                    ->label('Storefront URL')
                    ->badge()
                    ->color('info')
                    ->url(fn (Page $record): string => route('store.pages.show', $record->slug), shouldOpenInNewTab: true),

                IconColumn::make('is_active')
                    ->label('Published')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->defaultSort('title')
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
