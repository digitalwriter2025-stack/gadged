<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\Menus\Pages\EditMenu;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Schemas\PageForm;
use App\Models\Menu;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PageResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ContentSeeder::class);
        $this->actingAs(User::factory()->superAdmin()->create());
    }

    public function test_admin_can_create_a_page_and_show_it_in_the_footer(): void
    {
        Livewire::test(CreatePage::class)
            ->fillForm([
                'title' => 'Corporate Sales',
                'slug' => 'corporate-sales',
                'content' => '<p>Bulk orders welcome.</p>',
                'is_active' => true,
                'footer_location' => 'footer_information',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $page = Page::query()->where('slug', 'corporate-sales')->sole();
        $this->assertSame('footer_information', $page->footerLocation());

        $this->get('/')
            ->assertOk()
            ->assertSee('Corporate Sales')
            ->assertSee(route('store.pages.show', $page), escape: false);
    }

    public function test_page_without_a_footer_location_stays_out_of_the_footer(): void
    {
        Livewire::test(CreatePage::class)
            ->fillForm([
                'title' => 'Hidden Landing',
                'slug' => 'hidden-landing',
                'content' => '<p>Campaign.</p>',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertNull(Page::query()->where('slug', 'hidden-landing')->sole()->footerLocation());
        $this->get('/')->assertDontSee('Hidden Landing');
    }

    public function test_admin_can_move_a_page_between_footer_columns_and_remove_it(): void
    {
        $page = Page::query()->where('slug', 'privacy-policy')->sole();
        $page->update(['content' => '<p>Policy.</p>']);

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->assertSchemaStateSet(['footer_location' => 'footer_policies'])
            ->fillForm(['title' => 'Privacy Notice', 'footer_location' => 'footer_information'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('footer_information', $page->footerLocation());
        $this->assertSame(1, $page->menuItems()->count());
        $this->assertSame('Privacy Notice', $page->menuItems()->sole()->label);

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->fillForm(['footer_location' => PageForm::NOT_IN_FOOTER])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(0, $page->menuItems()->count());
    }

    public function test_deleting_a_page_removes_its_footer_link(): void
    {
        $page = Page::query()->where('slug', 'about-us')->sole();
        $this->assertSame(1, $page->menuItems()->count());

        $page->delete();

        $this->assertSame(0, Menu::query()->where('location', 'footer_information')->sole()->items()->where('label', 'About Us')->count());
    }

    public function test_footer_menu_with_page_links_can_be_saved_without_urls(): void
    {
        $menu = Menu::query()->where('location', 'footer_policies')->sole();

        Livewire::test(EditMenu::class, ['record' => $menu->getRouteKey()])
            ->call('save')
            ->assertHasNoFormErrors();
    }
}
