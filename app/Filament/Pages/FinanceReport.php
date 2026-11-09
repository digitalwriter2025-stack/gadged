<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Permission;
use App\Filament\Resources\LedgerEntries\LedgerEntryResource;
use App\Filament\Widgets\Finance\ExpenseBreakdownChart;
use App\Filament\Widgets\Finance\FinanceOrderStats;
use App\Filament\Widgets\Finance\FinanceProfitStats;
use App\Filament\Widgets\Finance\FinanceTrendChart;
use App\Filament\Widgets\Finance\ProfitLossStatement;
use App\Support\ReportPeriod;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

/**
 * The shop's accounts in one place: sales, earnings, pending and cancelled orders,
 * recorded income and expenses, and profit for a chosen period.
 */
class FinanceReport extends BaseDashboard
{
    use HasFiltersForm;

    protected static string $routePath = 'finance';

    protected static string|\UnitEnum|null $navigationGroup = 'Accounts';

    protected static ?string $navigationLabel = 'Finance Report';

    protected static ?string $title = 'Finance Report';

    protected static ?int $navigationSort = 1;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can(Permission::FinanceView->value);
    }

    public function getSubheading(): string
    {
        return 'বিক্রি, আয়, পেন্ডিং, বাতিল এবং আয়-ব্যয়ের সম্পূর্ণ হিসাব';
    }

    /**
     * @return array<class-string>
     */
    public function getWidgets(): array
    {
        return [
            FinanceOrderStats::class,
            FinanceProfitStats::class,
            FinanceTrendChart::class,
            ProfitLossStatement::class,
            ExpenseBreakdownChart::class,
        ];
    }

    /**
     * @return array<string, int>
     */
    public function getColumns(): int|array
    {
        return ['default' => 1, 'lg' => 2];
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'md' => 3])
                    ->schema([
                        Select::make('period')
                            ->label('Period')
                            ->options(ReportPeriod::presets())
                            ->default(ReportPeriod::THIS_MONTH)
                            ->selectablePlaceholder(false)
                            ->native(false),

                        DatePicker::make('start_date')
                            ->label('From')
                            ->native(false)
                            ->maxDate(now(config('app.display_timezone')))
                            ->visible(fn (Get $get): bool => $get('period') === ReportPeriod::CUSTOM),

                        DatePicker::make('end_date')
                            ->label('To')
                            ->native(false)
                            ->maxDate(now(config('app.display_timezone')))
                            ->visible(fn (Get $get): bool => $get('period') === ReportPeriod::CUSTOM),
                    ]),
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('recordEntry')
                ->label('Add income / expense')
                ->icon('heroicon-m-plus')
                ->url(fn (): string => LedgerEntryResource::getUrl('create'))
                ->visible(fn (): bool => LedgerEntryResource::canCreate()),

            Action::make('viewEntries')
                ->label('All entries')
                ->icon('heroicon-m-queue-list')
                ->color('gray')
                ->url(fn (): string => LedgerEntryResource::getUrl()),
        ];
    }
}
