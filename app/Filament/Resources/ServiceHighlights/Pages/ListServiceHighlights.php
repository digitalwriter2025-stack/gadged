<?php

namespace App\Filament\Resources\ServiceHighlights\Pages;

use App\Enums\HomeSectionType;
use App\Filament\Resources\ServiceHighlights\ServiceHighlightResource;
use App\Models\HomeSection;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListServiceHighlights extends ListRecords
{
    protected static string $resource = ServiceHighlightResource::class;

    public function getSubheading(): string|Htmlable
    {
        $description = 'Trust badges shown in the strip under the homepage slider, such as 0% EMI or Cash on delivery.';

        if ($this->isStripOnHomepage()) {
            return $description;
        }

        return new HtmlString(e($description).' <span class="gx-hl-warning">The strip is turned off on the homepage, so customers can\'t see these badges yet.</span>');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('showOnHomepage')
                ->label('Show on homepage')
                ->icon('heroicon-m-eye')
                ->color('warning')
                ->visible(fn (): bool => ! $this->isStripOnHomepage())
                ->action(function (): void {
                    $this->showStripOnHomepage();

                    Notification::make()
                        ->title('Service highlights are now live on the homepage')
                        ->success()
                        ->send();
                }),

            Action::make('viewOnSite')
                ->label('View on site')
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->color('gray')
                ->url(route('store.home'))
                ->openUrlInNewTab(),

            CreateAction::make()->icon('heroicon-m-plus'),
        ];
    }

    private function isStripOnHomepage(): bool
    {
        return HomeSection::query()
            ->where('type', HomeSectionType::ServiceHighlights)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Turns the strip's homepage section on, recreating it near the top of the homepage if it was deleted.
     * Saved through the model so the storefront cache is cleared.
     */
    private function showStripOnHomepage(): void
    {
        $section = HomeSection::query()->firstOrNew(
            ['type' => HomeSectionType::ServiceHighlights],
            ['settings' => [], 'sort_order' => 2],
        );

        $section->is_active = true;
        $section->save();
    }
}
