<?php

namespace Tests\Feature\Store;

use App\Enums\OrderStatus;
use App\Enums\ReviewStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_in_customer_sees_the_review_form(): void
    {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('store.products.show', $product->slug))
            ->assertOk()
            ->assertSee('Write a review')
            ->assertSee(route('store.reviews.store', $product->slug), false)
            ->assertDontSee('Login to write a review.');
    }

    public function test_guests_cannot_submit_a_review(): void
    {
        $product = Product::factory()->create();

        $this->post(route('store.reviews.store', $product->slug), $this->reviewData())
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_submitted_review_waits_for_approval(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($customer)
            ->post(route('store.reviews.store', $product->slug), $this->reviewData(['title' => 'Charges my laptop fast']))
            ->assertRedirect(route('store.products.show', $product->slug).'#reviews')
            ->assertSessionHas('status', 'review-submitted');

        $review = Review::query()->sole();
        $this->assertSame(ReviewStatus::Pending, $review->status);
        $this->assertSame($customer->id, $review->user_id);
        $this->assertSame(4, $review->rating);
        $this->assertFalse($review->is_verified_purchase);
        $this->assertSame(0, $product->fresh()->rating_count);

        $this->get(route('store.products.show', $product->slug))
            ->assertOk()
            ->assertSee('Thanks for your review!')
            ->assertDontSee('Charges my laptop fast')
            ->assertDontSee('Write a review');

        $this->get(route('store.products.show', $product->slug))
            ->assertOk()
            ->assertSee('Your review is awaiting approval.');
    }

    public function test_review_of_a_delivered_purchase_is_marked_verified(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->status(OrderStatus::Delivered)->create(['user_id' => $customer->id]);
        $item = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'regular_price' => '1000.00',
            'unit_price' => '1000.00',
            'quantity' => 1,
            'line_total' => '1000.00',
        ]);

        $this->actingAs($customer)
            ->post(route('store.reviews.store', $product->slug), $this->reviewData())
            ->assertSessionHasNoErrors();

        $review = Review::query()->sole();
        $this->assertTrue($review->is_verified_purchase);
        $this->assertSame($item->id, $review->order_item_id);
    }

    public function test_review_needs_a_rating_and_text(): void
    {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('store.reviews.store', $product->slug), ['rating' => 6, 'body' => 'Short'])
            ->assertRedirect(route('store.products.show', $product->slug).'#reviews')
            ->assertSessionHasErrors(['rating', 'body']);

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_customer_cannot_review_the_same_product_twice(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();
        Review::factory()->for($customer)->for($product)->create();

        $this->actingAs($customer)
            ->post(route('store.reviews.store', $product->slug), $this->reviewData())
            ->assertSessionHasErrors('review');

        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_rejected_review_can_be_rewritten_and_goes_back_for_approval(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();
        $review = Review::factory()->for($customer)->for($product)->create([
            'body' => 'Old text that was not approved.',
        ]);
        $review->forceFill(['status' => ReviewStatus::Rejected, 'rejection_reason' => 'Spam', 'moderated_at' => now()])->save();

        $this->actingAs($customer)
            ->get(route('store.products.show', $product->slug))
            ->assertSee('Your previous review was not approved.')
            ->assertSee('Write a review');

        $this->post(route('store.reviews.store', $product->slug), $this->reviewData(['body' => 'Rewritten honest opinion about it.']))
            ->assertSessionHasNoErrors();

        $review->refresh();
        $this->assertDatabaseCount('reviews', 1);
        $this->assertSame(ReviewStatus::Pending, $review->status);
        $this->assertSame('Rewritten honest opinion about it.', $review->body);
        $this->assertNull($review->rejection_reason);
        $this->assertNull($review->moderated_at);
    }

    public function test_unpublished_products_cannot_be_reviewed(): void
    {
        $product = Product::factory()->draft()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('store.reviews.store', $product->slug), $this->reviewData())
            ->assertNotFound();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function reviewData(array $overrides = []): array
    {
        return [
            'rating' => 4,
            'title' => 'Solid power bank',
            'body' => 'Holds charge well and fits in my bag.',
            ...$overrides,
        ];
    }
}
