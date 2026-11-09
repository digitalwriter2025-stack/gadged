<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\StaffRole;
use App\Enums\UserStatus;
use App\Filament\Pages\ManageSiteSettings;
use App\Models\Setting;
use App\Models\User;
use App\Services\MetaPixel;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MetaPixelSettingsTest extends TestCase
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

    public function test_meta_section_is_shown_on_the_site_settings_page(): void
    {
        $this->get('/admin/manage-site-settings')
            ->assertOk()
            ->assertSee('SEO &amp; Analytics', false)
            ->assertSee('Meta (Facebook)')
            ->assertSee('Meta Pixel ID (dataset ID)')
            ->assertSee('Conversions API access token')
            ->assertSee('Send test event');
    }

    public function test_settings_saved_by_the_admin_apply_to_the_storefront_immediately(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);
        $this->withoutDefer();

        $this->get(route('store.home'))->assertDontSee('fbevents.js', false);
        Http::assertNothingSent();

        Livewire::test(ManageSiteSettings::class)
            ->fillForm([
                'meta_pixel.pixel_id' => '1111111111111111',
                'meta_pixel.browser_enabled' => true,
                'meta_pixel.access_token' => 'EAAG-first-token',
                'meta_pixel.server_enabled' => true,
                'meta_pixel.test_event_code' => 'TEST11111',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get(route('store.home'))->assertSee('fbq(\'init\', "1111111111111111"', false);
        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/1111111111111111/events')
            && $request['access_token'] === 'EAAG-first-token'
            && $request['test_event_code'] === 'TEST11111');

        Livewire::test(ManageSiteSettings::class)
            ->fillForm([
                'meta_pixel.pixel_id' => '2222222222222222',
                'meta_pixel.access_token' => 'EAAG-second-token',
                'meta_pixel.test_event_code' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get(route('store.home'))
            ->assertSee('fbq(\'init\', "2222222222222222"', false)
            ->assertDontSee('1111111111111111', false);
        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/2222222222222222/events')
            && $request['access_token'] === 'EAAG-second-token'
            && ! isset($request['test_event_code']));

        Livewire::test(ManageSiteSettings::class)
            ->fillForm(['meta_pixel.browser_enabled' => false, 'meta_pixel.server_enabled' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        Http::fake();
        $this->get(route('store.home'))->assertDontSee('fbevents.js', false);
        Http::assertNothingSent();
    }

    public function test_access_token_is_saved_encrypted_and_kept_when_left_blank(): void
    {
        Livewire::test(ManageSiteSettings::class)
            ->fillForm([
                'meta_pixel.pixel_id' => '1234567890123456',
                'meta_pixel.access_token' => 'EAAG-secret-token',
                'meta_pixel.server_enabled' => true,
                'meta_pixel.domain_verification' => '<meta name="facebook-domain-verification" content="abc123xyz" />',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertSet('hasSavedMetaAccessToken', true)
            ->assertSet('data.meta_pixel.access_token', null);

        $stored = Setting::query()->where(['group' => 'meta_pixel', 'key' => 'access_token'])->value('value');
        $this->assertNotSame('EAAG-secret-token', $stored);
        $this->assertSame('EAAG-secret-token', app(MetaPixel::class)->accessToken());
        $this->assertSame('abc123xyz', app(SettingsService::class)->get('meta_pixel.domain_verification'));
        $this->assertTrue(app(MetaPixel::class)->serverEnabled());

        Livewire::test(ManageSiteSettings::class)
            ->assertSet('data.meta_pixel.access_token', null)
            ->assertDontSee('EAAG-secret-token')
            ->fillForm(['meta_pixel.test_event_code' => 'TEST12345'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('EAAG-secret-token', app(MetaPixel::class)->accessToken());
    }

    public function test_pixel_id_must_be_digits_and_server_events_need_a_token(): void
    {
        Livewire::test(ManageSiteSettings::class)
            ->fillForm([
                'meta_pixel.pixel_id' => 'abc-123',
                'meta_pixel.server_enabled' => true,
            ])
            ->call('save')
            ->assertHasFormErrors(['meta_pixel.pixel_id' => 'regex', 'meta_pixel.server_enabled']);
    }

    public function test_test_event_reports_what_meta_answered(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);
        app(SettingsService::class)->setMany('meta_pixel', [
            'pixel_id' => '1234567890123456',
            'access_token' => MetaPixel::encryptAccessToken('EAAG-secret-token'),
            'test_event_code' => 'TEST12345',
        ]);

        Livewire::test(ManageSiteSettings::class)
            ->call('sendMetaTestEvent')
            ->assertNotified('Meta received the test event');

        Http::assertSent(fn ($request): bool => $request['test_event_code'] === 'TEST12345'
            && $request['data'][0]['event_name'] === 'PageView');
    }

    public function test_test_event_shows_the_meta_error(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['message' => 'Invalid OAuth access token.']], 400)]);
        app(SettingsService::class)->setMany('meta_pixel', [
            'pixel_id' => '1234567890123456',
            'access_token' => MetaPixel::encryptAccessToken('expired-token'),
        ]);

        Livewire::test(ManageSiteSettings::class)
            ->call('sendMetaTestEvent')
            ->assertNotified('Meta rejected the test event');
    }
}
