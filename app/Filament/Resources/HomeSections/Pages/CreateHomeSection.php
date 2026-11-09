<?php

namespace App\Filament\Resources\HomeSections\Pages;

use App\Filament\Resources\HomeSections\HomeSectionResource;
use App\Support\Filament\SyncsMediaUploads;
use Filament\Resources\Pages\CreateRecord;

class CreateHomeSection extends CreateRecord
{
    use SyncsMediaUploads;

    protected static string $resource = HomeSectionResource::class;

    protected function afterCreate(): void
    {
        $this->replaceMediaCollection('images_upload', 'images');
    }
}
