<?php

declare(strict_types=1);

namespace App\Filament\Resources\Banners\Pages;

use App\Filament\Resources\Banners\BannerResource;
use App\Support\Filament\SyncsMediaUploads;
use Filament\Resources\Pages\CreateRecord;

class CreateBanner extends CreateRecord
{
    use SyncsMediaUploads;

    protected static string $resource = BannerResource::class;

    protected function afterCreate(): void
    {
        $this->syncMediaCollection('desktop_image', 'desktop');
        $this->syncMediaCollection('mobile_image', 'mobile');
    }
}
