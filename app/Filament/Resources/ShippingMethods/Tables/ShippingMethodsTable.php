<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingMethods\Tables;

use App\Models\ShippingMethod;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ShippingMethodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->width('60px'),

                TextColumn::make('name')
                    ->label('Method')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (ShippingMethod $record): ?string => $record->description),

                TextColumn::make('charge')
                    ->label('Cost')
                    ->money('BDT'),

                TextColumn::make('free_shipping_threshold')
                    ->label('Free Above')
                    ->money('BDT')
                    ->placeholder('—'),

                TextColumn::make('delivery_estimate')
                    ->label('Estimate')
                    ->placeholder('—'),

                ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordActions([
                EditAction::make(),
                ReplicateAction::make()
                    ->label('Duplicate')
                    ->excludeAttributes(['code'])
                    ->beforeReplicaSaved(function (ShippingMethod $replica): void {
                        $replica->name = "{$replica->name} (copy)";
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
