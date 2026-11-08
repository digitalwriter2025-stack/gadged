<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Support\Filament\SyncsMediaUploads;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    use SyncsMediaUploads;

    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['image_upload'] = $this->record->getFirstMedia('image')?->getPathRelativeToRoot();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncMediaCollection('image_upload', 'image');
    }
}
