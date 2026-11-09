<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Finance;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Widgets\Finance\Concerns\InteractsWithFinanceReport;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Sell / earn / pending / cancel cards for the selected period.
 */
class FinanceOrderStats extends StatsOverviewWidget
{
    use InteractsWithFinanceReport;

    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected ?string $heading = 'Orders · অর্ডারের হিসাব';

    protected function getDescription(): ?string
    {
        return $this->period()->label().' · Sales, pending and cancelled count orders placed in this period; earned counts orders delivered in it.';
    }

    /**
     * @return list<Stat>
     */
    protected function getStats(): array
    {
        $summary = $this->report()->orderSummary($this->period());

        return [
            Stat::make('Sales · বিক্রি', self::money($summary['sales']['amount']))
                ->icon('heroicon-o-shopping-bag')
                ->extraAttributes(['data-accent' => 'blue'])
                ->description(sprintf(
                    '%s orders · %s awaiting delivery',
                    number_format($summary['sales']['orders']),
                    self::money($summary['awaiting_delivery']['amount']),
                ))
                ->color('info'),

            Stat::make('Earned · আয়', self::money($summary['earned']['amount']))
                ->icon('heroicon-o-banknotes')
                ->extraAttributes(['data-accent' => 'emerald'])
                ->description(number_format($summary['earned']['orders']).' orders delivered')
                ->descriptionIcon('heroicon-m-check-circle')
                ->url(OrderResource::getUrl('index', ['tab' => OrderStatus::Delivered->value]))
                ->color('success'),

            Stat::make('Pending · পেন্ডিং', self::money($summary['pending']['amount']))
                ->icon('heroicon-o-clock')
                ->extraAttributes(['data-accent' => 'amber'])
                ->description(number_format($summary['pending']['orders']).' orders waiting for confirmation')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->url(OrderResource::getUrl('index', ['tab' => OrderStatus::Pending->value]))
                ->color('warning'),

            Stat::make('Cancelled · বাতিল', self::money($summary['cancelled']['amount']))
                ->icon('heroicon-o-x-circle')
                ->extraAttributes(['data-accent' => 'rose'])
                ->description(sprintf(
                    '%s cancelled · %s returned (%s)',
                    number_format($summary['cancelled']['orders']),
                    number_format($summary['returned']['orders']),
                    self::money($summary['returned']['amount']),
                ))
                ->url(OrderResource::getUrl('index', ['tab' => OrderStatus::Cancelled->value]))
                ->color('danger'),
        ];
    }
}
