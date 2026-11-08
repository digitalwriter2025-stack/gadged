<?php

namespace App\Filament\Resources\ServiceHighlights\Pages;

use App\Filament\Resources\ServiceHighlights\ServiceHighlightResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServiceHighlights extends ListRecords
{
    protected static string $resource = ServiceHighlightResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
