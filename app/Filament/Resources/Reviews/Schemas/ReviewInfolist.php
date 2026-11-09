<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reviews\Schemas;

use App\Enums\ReviewStatus;
use App\Models\Review;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

/**
 * Full review text with its customer, product and moderation history, shown in the list's view modal.
 */
class ReviewInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('product.name')
                    ->label('Product'),
                TextEntry::make('user.name')
                    ->label('Customer')
                    ->helperText(fn (Review $record): ?string => $record->user?->email ?? $record->user?->phone),
                TextEntry::make('rating')
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state).str_repeat('☆', 5 - $state))
                    ->color('warning'),
                IconEntry::make('is_verified_purchase')
                    ->label('Verified purchase')
                    ->boolean(),
                TextEntry::make('title')
                    ->placeholder('No title')
                    ->columnSpanFull(),
                TextEntry::make('body')
                    ->label('Review')
                    ->placeholder('No text')
                    ->extraAttributes(['style' => 'white-space: pre-line'])
                    ->columnSpanFull(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('created_at')
                    ->label('Submitted')
                    ->dateTime('d M Y, h:i A', config('app.display_timezone')),
                TextEntry::make('moderator.name')
                    ->label('Moderated by')
                    ->placeholder('Not moderated yet')
                    ->helperText(fn (Review $record): ?string => $record->moderated_at?->timezone(config('app.display_timezone'))->format('d M Y, h:i A')),
                TextEntry::make('rejection_reason')
                    ->label('Rejection reason')
                    ->placeholder('No reason given')
                    ->visible(fn (Review $record): bool => $record->status === ReviewStatus::Rejected),
            ]);
    }
}
