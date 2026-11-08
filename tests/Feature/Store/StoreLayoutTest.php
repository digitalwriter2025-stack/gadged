<?php

namespace Tests\Feature\Store;

use App\Models\Menu;
use App\Models\Page;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_social_and_footer_details_come_from_settings(): void
    {
        $settings = app(SettingsService::class);
        $settings->setMany('general', ['site_name' => 'Tech Haven']);
        $settings->setMany('contact', [
            'hotline' => '+880 1711-000000',
            'email' => 'hello@techhaven.test',
            'address' => 'Level 4, Gulshan Avenue, Dhaka',
            'whatsapp' => '+880 1711 000001',
            'map_url' => 'https://maps.example.test/tech-haven',
        ]);
        $settings->setMany('social', ['facebook' => 'https://facebook.example.test/techhaven']);
        $settings->setMany('footer', ['developer_name' => 'Studio Nine', 'developer_url' => 'https://studio-nine.test']);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('<title>Tech Haven</title>', false)
            ->assertSee('href="tel:+8801711000000"', false)
            ->assertSee('Hotline: +880 1711-000000')
            ->assertSee('mailto:hello@techhaven.test', false)
            ->assertSee('Level 4, Gulshan Avenue, Dhaka')
            ->assertSee('https://wa.me/8801711000001', false)
            ->assertSee('https://facebook.example.test/techhaven', false)
            ->assertSee('https://maps.example.test/tech-haven', false)
            ->assertDontSee('aria-label="YouTube"', false)
            ->assertDontSee('Complain Box')
            ->assertSee('Copyright © '.now()->year.', Tech Haven. All Rights Reserved.')
            ->assertSee('Studio Nine');
    }

    public function test_missing_contact_details_hide_their_buttons(): void
    {
        $this->get(route('store.home'))
            ->assertOk()
            ->assertDontSee('aria-label="Call us"', false)
            ->assertDontSee('aria-label="Chat on WhatsApp"', false)
            ->assertDontSee('Designed and Developed By');
    }

    public function test_footer_menus_link_to_published_pages_and_hide_unpublished_ones(): void
    {
        $privacy = Page::query()->create(['title' => 'Privacy Policy', 'slug' => 'privacy-policy', 'is_active' => true]);
        $draft = Page::query()->create(['title' => 'Warranty Policy', 'slug' => 'warranty-policy', 'is_active' => false]);

        $policies = Menu::query()->create(['name' => 'Policies', 'location' => 'footer_policies']);
        $policies->items()->create(['label' => 'Privacy Policy', 'linkable_type' => $privacy->getMorphClass(), 'linkable_id' => $privacy->id, 'sort_order' => 1]);
        $policies->items()->create(['label' => 'Warranty Policy', 'linkable_type' => $draft->getMorphClass(), 'linkable_id' => $draft->id, 'sort_order' => 2]);
        $policies->items()->create(['label' => 'Hidden Link', 'url' => '/hidden', 'is_active' => false, 'sort_order' => 3]);

        Menu::query()->create(['name' => 'Information', 'location' => 'footer_information'])
            ->items()->create(['label' => 'Brands', 'url' => '/brands', 'sort_order' => 1]);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSeeInOrder(['Information', 'Brands', 'Policies', 'Privacy Policy'])
            ->assertSee('href="'.route('store.pages.show', 'privacy-policy').'"', false)
            ->assertDontSee('Warranty Policy')
            ->assertDontSee('Hidden Link');
    }

    public function test_site_name_is_shown_when_no_logo_is_uploaded(): void
    {
        app(SettingsService::class)->set('general.site_name', 'Tech Haven');

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('>Tech Haven</span>', false);
    }
}
