<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reviews\Tables;

use App\Filament\Resources\Reviews\Actions\ApproveReviewAction;
use App\Filament\Resources\Reviews\Actions\RejectReviewAction;
use App\Models\Review;
use App\Services\ReviewService;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->weight('medium')
                    ->limit(40)
                    ->tooltip(fn (Review $record): ?string => $record->product?->name)
                    ->url(fn (Review $record): ?string => $record->product ? route('store.products.show', $record->product->slug).'#reviews' : null, shouldOpenInNewTab: true),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(['name', 'email', 'phone'])
                    ->description(fn (Review $record): ?string => $record->user?->email ?? $record->user?->phone),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state).str_repeat('☆', 5 - $state))
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('body')
                    ->label('Review')
                    ->formatStateUsing(fn (?string $state): string => Str::limit((string) $state, 90))
                    ->description(fn (Review $record): ?string => $record->title, position: 'above')
                    ->wrap()
                    ->searchable(['title', 'body']),

                IconColumn::make('is_verified_purchase')
                    ->label('Verified')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('rating')
                    ->options([5 => '5 stars', 4 => '4 stars', 3 => '3 stars', 2 => '2 stars', 1 => '1 star']),
                TernaryFilter::make('is_verified_purchase')
                    ->label('Verified purchase'),
            ])
            ->recordActions([
                ApproveReviewAction::make()->iconButton()->tooltip('Approve'),
                RejectReviewAction::make()->iconButton()->tooltip('Reject'),
                ViewAction::make()->iconButton()->tooltip('Read review'),
                DeleteAction::make()->iconButton()->tooltip('Delete'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approveSelected')
                        ->label('Approve selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->authorizeIndividualRecords('update')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $reviews = app(ReviewService::class);
                            $records->each(fn (Review $review) => $reviews->approve($review, auth()->user()));

                            Notification::make()->title('Reviews approved')->success()->send();
                        }),
                    BulkAction::make('rejectSelected')
                        ->label('Reject selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->authorizeIndividualRecords('update')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $reviews = app(ReviewService::class);
                            $records->each(fn (Review $review) => $reviews->reject($review, auth()->user()));

                            Notification::make()->title('Reviews rejected')->success()->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->emptyStateIcon('heroicon-o-star')
            ->emptyStateHeading('No reviews here')
            ->emptyStateDescription('Reviews customers write on product pages show up here for approval.');
    }
}
