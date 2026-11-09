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

class GoogleTagSettingsTest extends TestCase
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

    public function test_google_and_other_tag_fields_are_shown(): void
    {
        $this->get('/admin/manage-site-settings')
            ->assertOk()
            ->assertSee('Google Analytics ID (GA4)')
            ->assertSee('Google Ads Conversion Label')
            ->assertSee('Google Tag Manager ID')
            ->assertSee('Extra code for the page head')
            ->assertSee('Extra code after the body tag');
    }

    public function test_saved_ids_are_normalised_and_reach_the_storefront(): void
    {
        Livewire::test(ManageSiteSettings::class)
            ->fillForm([
                'analytics.ga4_measurement_id' => ' g-test12345 ',
                'analytics.google_ads_id' => 'aw-123456789',
                'analytics.google_ads_conversion_label' => 'AbC-D_efG-h12',
                'analytics.gtm_id' => 'gtm-abc1234',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = app(SettingsService::class);
        $settings->flush();

        $this->assertSame('G-TEST12345', $settings->get('analytics.ga4_measurement_id'));
        $this->assertSame('AW-123456789', $settings->get('analytics.google_ads_id'));
        $this->assertSame('GTM-ABC1234', $settings->get('analytics.gtm_id'));

        $this->get(route('store.home'))
            ->assertSee('gtag/js?id=G-TEST12345', false)
            ->assertSee('"adsConversion":"AW-123456789\/AbC-D_efG-h12"', false)
            ->assertSee('ns.html?id=GTM-ABC1234', false);
    }

    public function test_an_id_saved_in_the_old_single_field_is_moved_and_can_be_cleared(): void
    {
        app(SettingsService::class)->set('analytics.google_tag_id', 'GTM-OLD1234');

        Livewire::test(ManageSiteSettings::class)
            ->assertFormSet(['analytics.gtm_id' => 'GTM-OLD1234'])
            ->fillForm(['analytics.gtm_id' => null])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get(route('store.home'))->assertDontSee('googletagmanager.com', false);
    }

    public function test_malformed_ids_are_rejected(): void
    {
        Livewire::test(ManageSiteSettings::class)
            ->fillForm([
                'analytics.ga4_measurement_id' => 'UA-12345-1',
                'analytics.gtm_id' => 'G-TEST12345',
                'analytics.google_ads_conversion_label' => 'AbC-D_efG-h12',
            ])
            ->call('save')
            ->assertHasFormErrors([
                'analytics.ga4_measurement_id' => 'regex',
                'analytics.gtm_id' => 'regex',
                'analytics.google_ads_id' => 'required',
            ]);
    }
}
