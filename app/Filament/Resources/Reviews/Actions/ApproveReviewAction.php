<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reviews\Actions;

use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Services\ReviewService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

/**
 * Publishes a review on the product page and counts it in the product rating.
 */
class ApproveReviewAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'approve';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Approve')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->authorize('update')
            ->visible(fn (Review $record): bool => $record->status !== ReviewStatus::Approved)
            ->action(function (Review $record): void {
                app(ReviewService::class)->approve($record, auth()->user());

                Notification::make()
                    ->title('Review approved')
                    ->body('It is now visible on the product page.')
                    ->success()
                    ->send();
            });
    }
}
