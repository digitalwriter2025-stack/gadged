<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Support\Filament\SyncsMediaUploads;
use App\Support\Filament\SyncsProductPricing;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use SyncsMediaUploads;
    use SyncsProductPricing;

    protected static string $resource = ProductResource::class;

    public function areFormActionsSticky(): bool
    {
        return true;
    }

    protected function afterCreate(): void
    {
        $this->syncDefaultVariant();
        $this->syncMediaCollection('main_image', 'main');
        $this->syncMediaCollection('gallery_images', 'gallery', isMultiple: true);
    }
}
