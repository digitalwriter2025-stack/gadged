<?php

declare(strict_types=1);

namespace App\Filament\Resources\Brands\Pages;

use App\Filament\Resources\Brands\BrandResource;
use App\Support\Filament\SyncsMediaUploads;
use Filament\Resources\Pages\CreateRecord;

class CreateBrand extends CreateRecord
{
    use SyncsMediaUploads;

    protected static string $resource = BrandResource::class;

    protected function afterCreate(): void
    {
        $this->syncMediaCollection('logo_upload', 'logo');
    }
}
