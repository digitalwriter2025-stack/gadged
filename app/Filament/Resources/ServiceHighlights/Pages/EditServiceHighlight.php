<?php

namespace App\Filament\Resources\ServiceHighlights\Pages;

use App\Filament\Resources\ServiceHighlights\ServiceHighlightResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceHighlight extends EditRecord
{
    protected static string $resource = ServiceHighlightResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
