<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reviews\Pages;

use App\Enums\ReviewStatus;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\Review;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    public function getSubheading(): string
    {
        return 'Customer reviews stay hidden until you approve them. Approved reviews show on the product page and count towards its rating.';
    }

    /**
     * Opens on the moderation queue.
     */
    public function getDefaultActiveTab(): string
    {
        return ReviewStatus::Pending->value;
    }

    /**
     * One tab per moderation status, each with a live count.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $counts = Review::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $tabs = [];

        foreach (ReviewStatus::cases() as $status) {
            $tabs[$status->value] = Tab::make($status->getLabel())
                ->badge($counts->get($status->value) ?: null)
                ->badgeColor($status->getColor())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', $status));
        }

        $tabs['all'] = Tab::make('All')
            ->icon('heroicon-m-queue-list')
            ->badge($counts->sum() ?: null)
            ->badgeColor('gray');

        return $tabs;
    }
}
