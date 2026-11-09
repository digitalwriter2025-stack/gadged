<?php

namespace App\Filament\Resources\HomeSections\Pages;

use App\Filament\Resources\HomeSections\HomeSectionResource;
use App\Support\Filament\SyncsMediaUploads;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHomeSection extends EditRecord
{
    use SyncsMediaUploads;

    protected static string $resource = HomeSectionResource::class;

    public function getTitle(): string
    {
        return 'Edit: '.($this->record->title ?: $this->record->type->getLabel());
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewOnSite')
                ->label('View on site')
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->color('gray')
                ->url(route('store.home'))
                ->openUrlInNewTab(),

            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['images_upload'] = $this->mediaPaths('images');

        return $data;
    }

    protected function afterSave(): void
    {
        $this->replaceMediaCollection('images_upload', 'images');
    }
}
