<?php

declare(strict_types=1);

namespace App\Filament\Resources\Offers\Pages;

use App\Filament\Resources\Offers\OfferResource;
use App\Support\Filament\SyncsMediaUploads;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOffer extends EditRecord
{
    use SyncsMediaUploads;

    protected static string $resource = OfferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['poster_upload'] = $this->record->getFirstMedia('poster')?->getPathRelativeToRoot();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncMediaCollection('poster_upload', 'poster');
    }
}
