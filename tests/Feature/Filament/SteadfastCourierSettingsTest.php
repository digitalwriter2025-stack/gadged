<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\StaffRole;
use App\Enums\UserStatus;
use App\Filament\Pages\ManageSteadfastCourier;
use App\Models\Setting;
use App\Models\User;
use App\Services\SettingsService;
use App\Services\SteadfastCourier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SteadfastCourierSettingsTest extends TestCase
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

    public function test_page_shows_the_steadfast_settings(): void
    {
        $this->get('/admin/manage-steadfast-courier')
            ->assertOk()
            ->assertSee('Steadfast Courier API')
            ->assertSee('API Base URL')
            ->assertSee('Secret Key')
            ->assertSee(SteadfastCourier::DEFAULT_BASE_URL)
            ->assertSee('সংযোগ পরীক্ষা করুন');
    }

    public function test_saving_keys_stores_them_encrypted_and_shows_the_balance(): void
    {
        Http::fake(['portal.packzy.com/api/v1/get_balance' => Http::response(['status' => 200, 'current_balance' => 6495])]);

        Livewire::test(ManageSteadfastCourier::class)
            ->fillForm([
                'steadfast.enabled' => true,
                'steadfast.api_key' => 'api-key-123',
                'steadfast.secret_key' => 'secret-key-456',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertSet('data.steadfast.api_key', null)
            ->assertSet('data.steadfast.secret_key', null)
            ->assertNotified('Steadfast সংযুক্ত হয়েছে')
            ->assertSee('Connected')
            ->assertSee('বর্তমান ব্যালেন্স: ৳৬৪৯৫')
            ->assertDontSee('api-key-123')
            ->assertDontSee('secret-key-456');

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://portal.packzy.com/api/v1/get_balance'
            && $request->hasHeader('Api-Key', 'api-key-123')
            && $request->hasHeader('Secret-Key', 'secret-key-456'));

        $stored = Setting::query()->where(['group' => 'steadfast', 'key' => 'api_key'])->value('value');
        $this->assertNotSame('api-key-123', $stored);

        $steadfast = app(SteadfastCourier::class);
        $this->assertSame('api-key-123', $steadfast->apiKey());
        $this->assertSame('secret-key-456', $steadfast->secretKey());
        $this->assertTrue($steadfast->enabled());
        $this->assertTrue($steadfast->connected());
        $this->assertSame(6495.0, $steadfast->lastBalance());
    }

    public function test_test_connection_saves_and_checks_keys_typed_without_saving(): void
    {
        Http::fake(['portal.packzy.com/api/v1/get_balance' => Http::response(['status' => 200, 'current_balance' => 6495])]);

        Livewire::test(ManageSteadfastCourier::class)
            ->fillForm([
                'steadfast.api_key' => 'typed-api-key',
                'steadfast.secret_key' => 'typed-secret-key',
            ])
            ->call('testConnection')
            ->assertHasNoFormErrors()
            ->assertNotified('Steadfast সংযুক্ত হয়েছে')
            ->assertSee('বর্তমান ব্যালেন্স: ৳৬৪৯৫');

        Http::assertSent(fn (Request $request): bool => $request->hasHeader('Api-Key', 'typed-api-key')
            && $request->hasHeader('Secret-Key', 'typed-secret-key'));

        $this->assertSame('typed-api-key', app(SteadfastCourier::class)->apiKey());
        $this->assertTrue(app(SteadfastCourier::class)->connected());
    }

    public function test_blank_keys_keep_the_saved_ones(): void
    {
        $this->saveKeys('api-key-123', 'secret-key-456');
        Http::fake();

        Livewire::test(ManageSteadfastCourier::class)
            ->assertSet('data.steadfast.api_key', null)
            ->fillForm(['steadfast.enabled' => false])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Steadfast সেটিংস সেভ হয়েছে');

        Http::assertNothingSent();
        $this->assertSame('api-key-123', app(SteadfastCourier::class)->apiKey());
        $this->assertSame('secret-key-456', app(SteadfastCourier::class)->secretKey());
        $this->assertFalse(app(SteadfastCourier::class)->enabled());
    }

    public function test_auto_send_is_on_by_default_and_can_be_switched_off(): void
    {
        $this->assertTrue(app(SteadfastCourier::class)->sendsConfirmedOrders());

        Livewire::test(ManageSteadfastCourier::class)
            ->assertSet('data.steadfast.auto_send', true)
            ->fillForm(['steadfast.auto_send' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse(app(SteadfastCourier::class)->sendsConfirmedOrders());
    }

    public function test_enabling_needs_both_keys(): void
    {
        Livewire::test(ManageSteadfastCourier::class)
            ->fillForm(['steadfast.enabled' => true, 'steadfast.api_key' => 'api-key-123'])
            ->call('save')
            ->assertHasFormErrors(['steadfast.enabled']);
    }

    public function test_test_connection_reports_rejected_keys(): void
    {
        $this->saveKeys('wrong-key', 'wrong-secret');
        Http::fake(['portal.packzy.com/*' => Http::response(['message' => 'Unauthorized'], 401)]);

        Livewire::test(ManageSteadfastCourier::class)
            ->call('testConnection')
            ->assertNotified('Steadfast সংযোগ ব্যর্থ হয়েছে')
            ->assertDontSee('বর্তমান ব্যালেন্স');

        $this->assertFalse(app(SteadfastCourier::class)->connected());
    }

    public function test_test_connection_uses_the_saved_base_url(): void
    {
        $this->saveKeys('api-key-123', 'secret-key-456');
        app(SettingsService::class)->set('steadfast.base_url', 'https://sandbox.example.com/api/v1/');
        Http::fake(['sandbox.example.com/*' => Http::response(['status' => 200, 'current_balance' => 120.5])]);

        Livewire::test(ManageSteadfastCourier::class)
            ->call('testConnection')
            ->assertNotified('Steadfast সংযুক্ত হয়েছে')
            ->assertSee('বর্তমান ব্যালেন্স: ৳১২০.৫');

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://sandbox.example.com/api/v1/get_balance');
    }

    public function test_saved_keys_can_be_removed(): void
    {
        $this->saveKeys('api-key-123', 'secret-key-456');
        app(SettingsService::class)->setMany('steadfast', ['enabled' => true, 'balance' => 6495]);
        app(SettingsService::class)->flush();

        Livewire::test(ManageSteadfastCourier::class)
            ->assertSee('Key মুছে ফেলুন')
            ->call('removeKeys')
            ->assertNotified('Steadfast key মুছে ফেলা হয়েছে')
            ->assertSet('data.steadfast.enabled', false)
            ->assertSet('savedApiKeyHint', null)
            ->assertSet('savedSecretKeyHint', null)
            ->assertDontSee('Connected')
            ->assertDontSee('Key মুছে ফেলুন');

        $steadfast = app(SteadfastCourier::class);
        $this->assertNull($steadfast->apiKey());
        $this->assertNull($steadfast->secretKey());
        $this->assertFalse($steadfast->enabled());
        $this->assertNull($steadfast->lastBalance());
    }

    public function test_remove_keys_button_is_hidden_until_keys_are_saved(): void
    {
        $this->get('/admin/manage-steadfast-courier')
            ->assertOk()
            ->assertDontSee('Key মুছে ফেলুন');
    }

    private function saveKeys(string $apiKey, string $secretKey): void
    {
        app(SettingsService::class)->setMany('steadfast', [
            'api_key' => SteadfastCourier::encryptKey($apiKey),
            'secret_key' => SteadfastCourier::encryptKey($secretKey),
        ]);
        app(SettingsService::class)->flush();
    }
}
