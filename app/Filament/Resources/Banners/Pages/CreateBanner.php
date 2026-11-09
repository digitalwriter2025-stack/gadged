<?php

declare(strict_types=1);

namespace App\Filament\Resources\Banners\Pages;

use App\Filament\Resources\Banners\BannerResource;
use App\Filament\Resources\Banners\Schemas\BannerForm;
use App\Support\Filament\SyncsMediaUploads;
use Filament\Resources\Pages\CreateRecord;

class CreateBanner extends CreateRecord
{
    use SyncsMediaUploads;

    protected static string $resource = BannerResource::class;

    public function areFormActionsSticky(): bool
    {
        return true;
    }

    protected function afterCreate(): void
    {
        foreach (BannerForm::IMAGE_FIELDS as $field => $collection) {
            $this->syncMediaCollection($field, $collection);
        }
    }
}
