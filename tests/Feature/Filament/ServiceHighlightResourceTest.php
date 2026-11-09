<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\HomeSectionType;
use App\Filament\Resources\ServiceHighlights\Pages\CreateServiceHighlight;
use App\Filament\Resources\ServiceHighlights\Pages\ListServiceHighlights;
use App\Models\HomeSection;
use App\Models\ServiceHighlight;
use App\Models\User;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceHighlightResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ContentSeeder::class);
        $this->actingAs(User::factory()->superAdmin()->create());
    }

    public function test_admin_sees_each_highlight_as_a_compact_badge(): void
    {
        Livewire::test(ListServiceHighlights::class)
            ->assertOk()
            ->assertCanSeeTableRecords(ServiceHighlight::query()->get())
            ->assertSee('0% EMI')
            ->assertSee('gx-hl-icon', escape: false);
    }

    public function test_list_warns_when_the_strip_is_hidden_and_can_show_it_on_the_homepage(): void
    {
        $section = HomeSection::query()->where('type', HomeSectionType::ServiceHighlights)->sole();
        $this->assertFalse($section->is_active);

        Livewire::test(ListServiceHighlights::class)
            ->assertSee('turned off on the homepage')
            ->callAction('showOnHomepage')
            ->assertNotified();

        $this->assertTrue($section->refresh()->is_active);
    }

    public function test_show_on_homepage_action_is_hidden_once_the_strip_is_live(): void
    {
        HomeSection::query()->where('type', HomeSectionType::ServiceHighlights)->sole()->update(['is_active' => true]);

        Livewire::test(ListServiceHighlights::class)
            ->assertActionHidden('showOnHomepage')
            ->assertDontSee('turned off on the homepage');
    }

    public function test_show_on_homepage_action_adds_the_section_when_it_was_deleted(): void
    {
        HomeSection::query()->where('type', HomeSectionType::ServiceHighlights)->delete();

        Livewire::test(ListServiceHighlights::class)
            ->callAction('showOnHomepage');

        $this->assertTrue(HomeSection::query()->where('type', HomeSectionType::ServiceHighlights)->sole()->is_active);
    }

    public function test_admin_can_switch_a_highlight_off_from_the_list(): void
    {
        $highlight = ServiceHighlight::query()->where('title', '0% EMI')->sole();

        Livewire::test(ListServiceHighlights::class)
            ->call('updateTableColumnState', 'is_active', (string) $highlight->getKey(), false);

        $this->assertFalse($highlight->refresh()->is_active);
    }

    public function test_create_form_previews_the_badge_as_the_storefront_shows_it(): void
    {
        Livewire::test(CreateServiceHighlight::class)
            ->fillForm([
                'icon' => 'shield',
                'title' => 'Official Warranty',
                'subtitle' => '100% genuine products',
            ])
            ->assertSee('gx-hl-preview', escape: false)
            ->assertSee('100% genuine products')
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame('shield', ServiceHighlight::query()->where('title', 'Official Warranty')->sole()->icon);
    }
}
