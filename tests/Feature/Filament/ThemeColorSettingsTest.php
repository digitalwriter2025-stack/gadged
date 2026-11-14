<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\StaffRole;
use App\Enums\UserStatus;
use App\Filament\Pages\ManageSiteSettings;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ThemeColorSettingsTest extends TestCase
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

    public function test_theme_color_fields_are_shown(): void
    {
        $this->get('/admin/manage-site-settings')
            ->assertOk()
            ->assertSee('Theme Colors')
            ->assertSee('Button Color')
            ->assertSee('Button Hover Color')
            ->assertSee('Primary Brand Color')
            ->assertSee('Footer Background');
    }

    public function test_saved_colors_reach_the_storefront_as_theme_variables(): void
    {
        Livewire::test(ManageSiteSettings::class)
            ->fillForm([
                'theme.button' => '#2563EB',
                'theme.button_hover' => '#1d4ed8',
                'theme.topbar' => '#0f172a',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('--color-button:#2563eb;', false)
            ->assertSee('--color-button-hover:#1d4ed8;', false)
            ->assertSee('--color-button-text:#ffffff;', false)
            ->assertSee('--color-topbar:#0f172a;', false)
            ->assertSee('<meta name="theme-color" content="#ffffff">', false)
            ->assertSee('<meta name="apple-mobile-web-app-status-bar-style" content="default">', false);
    }

    public function test_a_color_that_is_not_hex_is_rejected(): void
    {
        Livewire::test(ManageSiteSettings::class)
            ->fillForm(['theme.button' => 'red;}</style><script>alert(1)</script>'])
            ->call('save')
            ->assertHasFormErrors(['theme.button']);
    }

    public function test_reset_restores_the_default_colors_in_the_form(): void
    {
        app(SettingsService::class)->set('theme.button', '#2563eb');

        Livewire::test(ManageSiteSettings::class)
            ->assertFormSet(['theme.button' => '#2563eb'])
            ->call('resetThemeColors')
            ->assertFormSet(['theme.button' => '#df0100', 'theme.button_hover' => '#b30000']);
    }

    public function test_an_unsafe_stored_value_is_never_printed(): void
    {
        $settings = app(SettingsService::class);
        $settings->setMany('theme', ['button' => 'red;}</style><script>alert(1)</script>', 'unknown' => '#123456']);
        $settings->flush();

        $this->get(route('store.home'))
            ->assertOk()
            ->assertDontSee('alert(1)', false)
            ->assertDontSee('--color-unknown', false)
            ->assertDontSee('--color-button:', false)
            ->assertSee('--color-accent:#df0100;', false);
    }
}
