<?php

namespace Tests\Feature\Store;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_page_renders_its_content(): void
    {
        Page::query()->create([
            'title' => 'Delivery Policy',
            'slug' => 'delivery-policy',
            'content' => '<h2>Inside Dhaka</h2><p>Delivered within 24 hours.</p>',
            'is_active' => true,
            'seo_title' => 'Delivery Policy and Charges',
        ]);

        $this->get(route('store.pages.show', 'delivery-policy'))
            ->assertOk()
            ->assertSee('<title>Delivery Policy and Charges | '.config('app.name').'</title>', false)
            ->assertSee('<h2>Inside Dhaka</h2>', false)
            ->assertSee('Delivered within 24 hours.');
    }

    public function test_page_content_is_sanitized(): void
    {
        Page::query()->create([
            'title' => 'About Us',
            'slug' => 'about-us',
            'content' => '<p>About</p><script>alert(1)</script><a href="javascript:alert(2)" onclick="steal()">x</a>',
            'is_active' => true,
        ]);

        $this->get(route('store.pages.show', 'about-us'))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('javascript:alert(2)', false)
            ->assertDontSee('onclick="steal()"', false);
    }

    public function test_unpublished_page_is_not_found(): void
    {
        Page::query()->create(['title' => 'Draft', 'slug' => 'draft', 'is_active' => false]);

        $this->get(route('store.pages.show', 'draft'))->assertNotFound();
    }
}
