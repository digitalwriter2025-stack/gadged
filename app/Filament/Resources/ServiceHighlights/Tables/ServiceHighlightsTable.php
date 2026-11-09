<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServiceHighlights\Tables;

use App\Models\ServiceHighlight;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
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

                TextColumn::make('title')
                    ->label('Badge')
                    ->searchable(['title', 'title_bn', 'subtitle'])
                    ->formatStateUsing(fn (string $state, ServiceHighlight $record): HtmlString => new HtmlString(
                        view('filament.service-highlights.badge', [
                            'icon' => $record->icon,
                            'title' => $state,
                            'subtitle' => $record->subtitle,
                        ])->render()
                    )),

                TextColumn::make('title_bn')
                    ->label('Bangla')
                    ->placeholder('Not translated')
                    ->toggleable(),

                TextColumn::make('url')
                    ->label('Link')
                    ->placeholder('No link')
                    ->color('gray')
                    ->limit(40)
                    ->toggleable(),

                ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->emptyStateIcon('heroicon-o-sparkles')
            ->emptyStateHeading('No service highlights yet')
            ->emptyStateDescription('Add badges such as 0% EMI or Cash on delivery to build the strip under the homepage slider.')
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
