<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\StaffRole;
use App\Enums\UserStatus;
use App\Filament\Pages\ManageSiteSettings;
use App\Models\User;
use App\Support\Localization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HeaderQuickLinkSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate(StaffRole::SuperAdmin->value);

        $admin = User::factory()->create(['status' => UserStatus::Active]);
        $admin->assignRole(StaffRole::SuperAdmin->value);

        $this->actingAs($admin);
    }

    public function test_header_shows_the_default_quick_links(): void
    {
        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('Order Track')
            ->assertSee('Where is my order')
            ->assertSee('Bundle Deals')
            ->assertSee('href="'.route('store.combos.index').'"', false);
    }

    public function test_admin_can_rename_and_repoint_the_combo_link(): void
    {
        Livewire::test(ManageSiteSettings::class)
            ->assertFormSet(['header.combo_enabled' => true])
            ->fillForm([
                'header.combo_label' => 'Mega Deals',
                'header.combo_label_bn' => 'মেগা ডিল',
                'header.combo_caption' => 'Save on bundles',
                'header.combo_url' => '/offers',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('Mega Deals')
            ->assertSee('Save on bundles')
            ->assertSee('href="/offers"', false)
            ->assertDontSee('Bundle Deals');

        $this->withCookie(Localization::COOKIE, 'bn')->get(route('store.home'))
            ->assertOk()
            ->assertSee('মেগা ডিল');
    }

    public function test_admin_can_hide_the_combo_link(): void
    {
        Livewire::test(ManageSiteSettings::class)
            ->fillForm(['header.combo_enabled' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get(route('store.home'))
            ->assertOk()
            ->assertDontSee('Bundle Deals')
            ->assertSee('Where is my order');
    }

    public function test_a_script_url_is_rejected(): void
    {
        Livewire::test(ManageSiteSettings::class)
            ->fillForm(['header.combo_url' => 'javascript:alert(1)'])
            ->call('save')
            ->assertHasFormErrors(['header.combo_url']);
    }
}
