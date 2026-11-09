<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reviews\Actions;

use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Services\ReviewService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

/**
 * Hides a review from the storefront. The customer may then write a new one.
 */
class RejectReviewAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'reject';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Reject')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->authorize('update')
            ->visible(fn (Review $record): bool => $record->status !== ReviewStatus::Rejected)
            ->modalHeading('Reject review')
            ->modalDescription('The review will be hidden from the product page.')
            ->modalSubmitActionLabel('Reject review')
            ->modalWidth('lg')
            ->schema([
                Textarea::make('reason')
                    ->label('Reason')
                    ->placeholder('Optional, for staff reference')
                    ->rows(3)
                    ->maxLength(255),
            ])
            ->action(function (Review $record, array $data): void {
                app(ReviewService::class)->reject($record, auth()->user(), $data['reason'] ?? null);

                Notification::make()
                    ->title('Review rejected')
                    ->success()
                    ->send();
            });
    }
}
