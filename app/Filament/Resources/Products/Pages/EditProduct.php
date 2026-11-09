<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Enums\ProductStatus;
use App\Filament\Resources\Products\ProductResource;
use App\Support\Filament\SyncsMediaUploads;
use App\Support\Filament\SyncsProductPricing;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use SyncsMediaUploads;
    use SyncsProductPricing;

    protected static string $resource = ProductResource::class;

    public function areFormActionsSticky(): bool
    {
        return true;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewOnStore')
                ->label('View on store')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (): string => route('store.products.show', $this->record))
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->record->status === ProductStatus::Active),

            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['main_image'] = $this->record->getFirstMedia('main')?->getPathRelativeToRoot();

        return [...$data, ...$this->pricingFormData($this->record)];
    }

    protected function afterSave(): void
    {
        $this->syncDefaultVariant();
        $this->syncMediaCollection('main_image', 'main');
        $this->syncMediaCollection('gallery_images', 'gallery', isMultiple: true);
    }
}
