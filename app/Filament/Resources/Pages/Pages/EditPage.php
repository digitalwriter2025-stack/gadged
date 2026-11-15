<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Pages\Schemas\PageForm;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

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
                ->url(fn (): string => route('store.pages.show', $this->record))
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->record->is_active),

            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['footer_location'] = $this->record->footerLocation() ?? PageForm::NOT_IN_FOOTER;

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->placeInFooter($this->data['footer_location'] ?? null);
    }
}
