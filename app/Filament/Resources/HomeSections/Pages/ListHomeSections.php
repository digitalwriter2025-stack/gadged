<?php

namespace App\Filament\Resources\HomeSections\Pages;

use App\Filament\Resources\HomeSections\HomeSectionResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHomeSections extends ListRecords
{
    protected static string $resource = HomeSectionResource::class;

    public function getSubheading(): string
    {
        return 'Each row is one block of the homepage, listed top to bottom: #1 is the first thing customers see. Use the ↑ ↓ buttons (or "Drag to reorder") to move a block, and the switch to show or hide it.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewOnSite')
                ->label('View on site')
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->color('gray')
                ->url(route('store.home'))
                ->openUrlInNewTab(),

            CreateAction::make()->icon('heroicon-m-plus'),
        ];
    }
}
