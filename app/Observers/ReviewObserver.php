<?php

namespace App\Observers;

use App\Models\Review;
use App\Services\ProductAggregateService;

/**
 * Keeps the product's rating read model in sync with approved reviews.
 */
class ReviewObserver
{
    public function __construct(private readonly ProductAggregateService $aggregates) {}

    public function saved(Review $review): void
    {
        if ($review->wasRecentlyCreated || $review->wasChanged(['status', 'rating'])) {
            $this->aggregates->refreshRating($review->product_id);
        }
    }

    public function deleted(Review $review): void
    {
        $this->aggregates->refreshRating($review->product_id);
    }
}
