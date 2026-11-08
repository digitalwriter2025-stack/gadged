<?php

declare(strict_types=1);

namespace App\Filament\Resources\FlashSales\Tables;

use App\Models\FlashSale;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FlashSalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Campaign')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('status')
                    ->label('Campaign Status')
                    ->badge()
                    ->state(fn (FlashSale $record): string => match (true) {
                        ! $record->is_active => 'Disabled',
                        $record->isRunning() => 'Running Now',
                        now()->lt($record->starts_at) => 'Scheduled',
                        default => 'Expired',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Running Now' => 'success',
                        'Scheduled' => 'info',
                        'Disabled' => 'gray',
                        default => 'danger',
                    }),

                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Deals Count')
                    ->badge()
                    ->color('warning'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('starts_at')
                    ->label('Starts At')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label('Ends At')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->defaultSort('starts_at', 'desc')
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
