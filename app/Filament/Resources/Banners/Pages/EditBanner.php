<?php

declare(strict_types=1);

namespace App\Filament\Resources\Banners\Pages;

use App\Filament\Resources\Banners\BannerResource;
use App\Support\Filament\SyncsMediaUploads;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBanner extends EditRecord
{
    use SyncsMediaUploads;

    protected static string $resource = BannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['desktop_image'] = $this->record->getFirstMedia('desktop')?->getPathRelativeToRoot();
        $data['mobile_image'] = $this->record->getFirstMedia('mobile')?->getPathRelativeToRoot();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncMediaCollection('desktop_image', 'desktop');
        $this->syncMediaCollection('mobile_image', 'mobile');
    }
}
