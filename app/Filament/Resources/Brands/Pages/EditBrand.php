<?php

declare(strict_types=1);

namespace App\Filament\Resources\Brands\Pages;

use App\Filament\Resources\Brands\BrandResource;
use App\Support\Filament\SyncsMediaUploads;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBrand extends EditRecord
{
    use SyncsMediaUploads;

    protected static string $resource = BrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['logo_upload'] = $this->record->getFirstMedia('logo')?->getPathRelativeToRoot();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncMediaCollection('logo_upload', 'logo');
    }
}
