<?php

declare(strict_types=1);

namespace App\Filament\Resources\Banners\Pages;

use App\Filament\Resources\Banners\BannerResource;
use App\Filament\Resources\Banners\Schemas\BannerForm;
use App\Support\Filament\SyncsMediaUploads;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBanner extends EditRecord
{
    use SyncsMediaUploads;

    protected static string $resource = BannerResource::class;

    public function areFormActionsSticky(): bool
    {
        return true;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach (BannerForm::IMAGE_FIELDS as $field => $collection) {
            $data[$field] = $this->record->getFirstMedia($collection)?->getPathRelativeToRoot();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        foreach (BannerForm::IMAGE_FIELDS as $field => $collection) {
            $this->syncMediaCollection($field, $collection);
        }
    }
}
