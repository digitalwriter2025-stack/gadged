<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Support\Filament\SyncsMediaUploads;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    use SyncsMediaUploads;

    protected static string $resource = CategoryResource::class;

    protected function afterCreate(): void
    {
        $this->syncMediaCollection('image_upload', 'image');
    }
}
