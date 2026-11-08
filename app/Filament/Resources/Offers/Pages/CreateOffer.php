<?php

declare(strict_types=1);

namespace App\Filament\Resources\Offers\Pages;

use App\Filament\Resources\Offers\OfferResource;
use App\Support\Filament\SyncsMediaUploads;
use Filament\Resources\Pages\CreateRecord;

class CreateOffer extends CreateRecord
{
    use SyncsMediaUploads;

    protected static string $resource = OfferResource::class;

    protected function afterCreate(): void
    {
        $this->syncMediaCollection('poster_upload', 'poster');
    }
}
