<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Support\Filament\SyncsMediaUploads;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use SyncsMediaUploads;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['main_image'] = $this->record->getFirstMedia('main')?->getPathRelativeToRoot();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncMediaCollection('main_image', 'main');
        $this->syncMediaCollection('gallery_images', 'gallery', isMultiple: true);
    }
}
