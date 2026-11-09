<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ReviewStatus;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use InvalidArgumentException;

/**
 * Customer review submission and staff moderation. Every review waits for staff
 * approval before it shows on the storefront; ReviewObserver keeps the product
 * rating in sync with the approved ones.
 */
class ReviewService
{
    /**
     * A customer may hold one review per product. A rejected review can be rewritten,
     * which sends it back to the moderation queue.
     *
     * @param  array{rating: int, title?: ?string, body: string}  $data
     *
     * @throws InvalidArgumentException when the customer already has a pending or approved review.
     */
    public function submit(User $user, Product $product, array $data): Review
    {
        $review = Review::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($review !== null && $review->status !== ReviewStatus::Rejected) {
            throw new InvalidArgumentException(__('You have already reviewed this product.'));
        }

        $deliveredItemId = $this->deliveredOrderItemId($user, $product);

        $review ??= new Review(['user_id' => $user->id, 'product_id' => $product->id]);

        $review->fill([
            'rating' => $data['rating'],
            'title' => filled($data['title'] ?? null) ? $data['title'] : null,
            'body' => $data['body'],
            'order_item_id' => $deliveredItemId,
            'is_verified_purchase' => $deliveredItemId !== null,
        ]);

        $review->forceFill([
            'status' => ReviewStatus::Pending,
            'rejection_reason' => null,
            'moderated_by' => null,
            'moderated_at' => null,
        ])->save();

        return $review;
    }

    public function approve(Review $review, User $moderator): void
    {
        $review->forceFill([
            'status' => ReviewStatus::Approved,
            'rejection_reason' => null,
            'moderated_by' => $moderator->id,
            'moderated_at' => now(),
        ])->save();
    }

    public function reject(Review $review, User $moderator, ?string $reason = null): void
    {
        $review->forceFill([
            'status' => ReviewStatus::Rejected,
            'rejection_reason' => filled($reason) ? $reason : null,
            'moderated_by' => $moderator->id,
            'moderated_at' => now(),
        ])->save();
    }

    /**
     * The customer's latest delivered order line for this product, which marks the review as a verified purchase.
     */
    private function deliveredOrderItemId(User $user, Product $product): ?int
    {
        return OrderItem::query()
            ->where('product_id', $product->id)
            ->whereHas('order', fn ($query) => $query->where('user_id', $user->id)->where('status', OrderStatus::Delivered))
            ->latest('id')
            ->value('id');
    }
}
