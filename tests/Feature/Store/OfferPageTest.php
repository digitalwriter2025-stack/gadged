<?php

namespace Tests\Feature\Store;

use App\Models\Offer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_offers_page_lists_only_running_offers(): void
    {
        Offer::factory()->create(['title' => 'Camera Deal', 'summary' => 'Buy camera and get discount', 'availability' => 'All Outlet']);
        Offer::factory()->expired()->create(['title' => 'Old Expired Deal']);
        Offer::factory()->inactive()->create(['title' => 'Hidden Draft Deal']);

        $this->get(route('store.offers.index'))
            ->assertOk()
            ->assertSeeInOrder(['Home', 'Offers'])
            ->assertSee('Camera Deal')
            ->assertSee('Buy camera and get discount')
            ->assertSee('All Outlet')
            ->assertSee('View Details')
            ->assertDontSee('Old Expired Deal')
            ->assertDontSee('Hidden Draft Deal');
    }

    public function test_mobile_deal_page_lists_only_mobile_deals(): void
    {
        Offer::factory()->mobileDeal()->create(['title' => 'Smartphone Bonanza']);
        Offer::factory()->create(['title' => 'Desktop Offer']);

        $this->get(route('store.offers.mobile'))
            ->assertOk()
            ->assertSee('Smartphone Bonanza')
            ->assertDontSee('Desktop Offer');
    }

    public function test_offers_page_shows_empty_state(): void
    {
        $this->get(route('store.offers.index'))->assertOk()->assertSee('No offers running right now');
    }

    public function test_offer_detail_page_shows_offer_and_its_published_products(): void
    {
        $offer = Offer::factory()->create(['title' => 'Monitor Mega Deal']);
        $offer->products()->attach([
            Product::factory()->create(['name' => 'Linked Monitor'])->id,
            Product::factory()->draft()->create(['name' => 'Draft Monitor'])->id,
        ]);
        Product::factory()->create(['name' => 'Unlinked Fridge']);

        $this->get(route('store.offers.show', $offer->slug))
            ->assertOk()
            ->assertSeeInOrder(['Home', 'Offers', 'Monitor Mega Deal'])
            ->assertSee('Offer Products')
            ->assertSee('Linked Monitor')
            ->assertDontSee('Draft Monitor')
            ->assertDontSee('Unlinked Fridge');
    }

    public function test_expired_inactive_and_missing_offers_return_not_found(): void
    {
        $this->get(route('store.offers.show', Offer::factory()->expired()->create()->slug))->assertNotFound();
        $this->get(route('store.offers.show', Offer::factory()->inactive()->create()->slug))->assertNotFound();
        $this->get(route('store.offers.show', 'missing-offer'))->assertNotFound();
    }

    public function test_header_links_to_offer_pages(): void
    {
        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee(route('store.offers.index'))
            ->assertSee(route('store.offers.mobile'));
    }
}
