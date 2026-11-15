<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    public function areFormActionsSticky(): bool
    {
        return true;
    }

    protected function afterCreate(): void
    {
        $this->record->placeInFooter($this->data['footer_location'] ?? null);
    }
}
