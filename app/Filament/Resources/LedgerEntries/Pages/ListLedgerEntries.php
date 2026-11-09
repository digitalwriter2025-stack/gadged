<?php

namespace App\Filament\Resources\LedgerEntries\Pages;

use App\Enums\LedgerEntryType;
use App\Filament\Pages\FinanceReport;
use App\Filament\Resources\LedgerEntries\LedgerEntryResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLedgerEntries extends ListRecords
{
    protected static string $resource = LedgerEntryResource::class;

    public function getSubheading(): string
    {
        return 'আয়-ব্যয়ের খাতা: rent, salary, ads, courier bills, stock purchases and other income. Website order sales are counted automatically in the Finance Report.';
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $tabs = ['all' => Tab::make('All')->icon('heroicon-m-queue-list')];

        foreach (LedgerEntryType::cases() as $type) {
            $tabs[$type->value] = Tab::make($type->getLabel())
                ->icon($type->getIcon())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', $type));
        }

        return $tabs;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('financeReport')
                ->label('Finance report')
                ->icon('heroicon-m-presentation-chart-line')
                ->color('gray')
                ->url(fn (): string => FinanceReport::getUrl())
                ->visible(fn (): bool => FinanceReport::canAccess()),
            CreateAction::make()
                ->label('Add entry'),
        ];
    }
}
