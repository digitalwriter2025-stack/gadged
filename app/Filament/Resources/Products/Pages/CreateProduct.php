<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Support\Filament\SyncsMediaUploads;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use SyncsMediaUploads;

    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        $this->syncMediaCollection('main_image', 'main');
        $this->syncMediaCollection('gallery_images', 'gallery', isMultiple: true);
    }
}
