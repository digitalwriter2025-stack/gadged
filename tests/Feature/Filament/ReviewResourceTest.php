<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\ReviewStatus;
use App\Enums\StaffRole;
use App\Filament\Resources\Reviews\Actions\ApproveReviewAction;
use App\Filament\Resources\Reviews\Actions\RejectReviewAction;
use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReviewResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_opens_on_pending_reviews(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $pending = Review::factory()->create(['title' => 'Waiting for approval']);
        $approved = Review::factory()->approved()->create();

        Livewire::test(ListReviews::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$approved])
            ->assertSee('Waiting for approval');
    }

    public function test_admin_can_read_the_full_review(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $review = Review::factory()->create(['body' => 'The full review text the admin needs to read before approving.']);

        Livewire::test(ListReviews::class)
            ->mountAction(TestAction::make('view')->table($review))
            ->assertSee('The full review text the admin needs to read before approving.')
            ->assertSee($review->product->name)
            ->assertSee($review->user->name);
    }

    public function test_pending_reviews_are_counted_in_the_navigation_badge(): void
    {
        Review::factory()->count(2)->create();
        Review::factory()->approved()->create();

        $this->assertSame('2', ReviewResource::getNavigationBadge());
    }

    public function test_approving_a_review_publishes_it_and_updates_the_rating(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $this->actingAs($admin);
        $product = Product::factory()->create();
        Review::factory()->approved()->for($product)->create(['rating' => 5]);
        $review = Review::factory()->for($product)->create(['rating' => 2, 'title' => 'Gets warm while charging']);

        Livewire::test(ListReviews::class)
            ->callAction(TestAction::make(ApproveReviewAction::getDefaultName())->table($review))
            ->assertHasNoFormErrors();

        $review->refresh();
        $this->assertSame(ReviewStatus::Approved, $review->status);
        $this->assertSame($admin->id, $review->moderated_by);
        $this->assertNotNull($review->moderated_at);

        $product->refresh();
        $this->assertSame(2, $product->rating_count);
        $this->assertEquals(3.5, $product->rating_avg);

        $this->get(route('store.products.show', $product->slug))
            ->assertSee('Gets warm while charging')
            ->assertSee('Reviews (2)');
    }

    public function test_rejecting_an_approved_review_hides_it_and_updates_the_rating(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $product = Product::factory()->create();
        $review = Review::factory()->approved()->for($product)->create(['title' => 'Off-topic rant']);

        Livewire::test(ListReviews::class)
            ->set('activeTab', ReviewStatus::Approved->value)
            ->callAction(TestAction::make(RejectReviewAction::getDefaultName())->table($review), ['reason' => 'Not about the product'])
            ->assertHasNoFormErrors();

        $review->refresh();
        $this->assertSame(ReviewStatus::Rejected, $review->status);
        $this->assertSame('Not about the product', $review->rejection_reason);
        $this->assertSame(0, $product->fresh()->rating_count);

        $this->get(route('store.products.show', $product->slug))
            ->assertDontSee('Off-topic rant');
    }

    public function test_deleting_a_review_updates_the_rating(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $product = Product::factory()->create();
        $review = Review::factory()->approved()->for($product)->create();
        $this->assertSame(1, $product->fresh()->rating_count);

        Livewire::test(ListReviews::class)
            ->set('activeTab', 'all')
            ->callAction(TestAction::make('delete')->table($review));

        $this->assertModelMissing($review);
        $this->assertSame(0, $product->fresh()->rating_count);
    }

    public function test_bulk_approve_publishes_every_selected_review(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $reviews = Review::factory()->count(3)->create();

        Livewire::test(ListReviews::class)
            ->selectTableRecords($reviews)
            ->callAction(TestAction::make('approveSelected')->table()->bulk())
            ->assertHasNoFormErrors();

        $this->assertSame(3, Review::query()->approved()->count());
    }

    public function test_staff_without_review_permission_cannot_open_reviews(): void
    {
        $this->actingAs(User::factory()->staff(StaffRole::OrderManager)->create());

        $this->get(ReviewResource::getUrl('index'))->assertForbidden();
    }
}
