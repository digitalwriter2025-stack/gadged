<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getSubheading(): string
    {
        return 'New website orders appear here automatically. Open an order to review it and move it through delivery.';
    }

    /**
     * One tab per working status, each with a live count.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $counts = Order::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $tabs = [
            'all' => Tab::make('All')
                ->icon('heroicon-m-queue-list')
                ->badge($counts->sum() ?: null)
                ->badgeColor('gray'),
        ];

        $statuses = [
            OrderStatus::Pending, OrderStatus::Confirmed, OrderStatus::Processing, OrderStatus::Packed,
            OrderStatus::Shipped, OrderStatus::Delivered, OrderStatus::Cancelled,
        ];

        foreach ($statuses as $status) {
            $tabs[$status->value] = Tab::make($status->getLabel())
                ->badge($counts->get($status->value) ?: null)
                ->badgeColor($status->getColor())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', $status));
        }

        $returns = [OrderStatus::Returned, OrderStatus::Refunded];

        $tabs['returns'] = Tab::make('Returns')
            ->badge($counts->only(array_map(fn (OrderStatus $status): string => $status->value, $returns))->sum() ?: null)
            ->badgeColor('danger')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('status', $returns));

        return $tabs;
    }
}
