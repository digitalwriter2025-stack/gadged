<?php

namespace Tests\Feature\Settings;

use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Tests\TestCase;

class SettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_defaults_come_from_config_until_saved(): void
    {
        $settings = app(SettingsService::class);

        $this->assertSame('GD', $settings->get('orders.number_prefix'));
        $this->assertNull($settings->get('contact.phone'));
        $this->assertSame('fallback', $settings->get('unknown.key', 'fallback'));
    }

    public function test_saved_values_override_defaults_and_keep_their_type(): void
    {
        $settings = app(SettingsService::class);

        $settings->setMany('contact', ['phone' => '09613828201', 'email' => 'hello@example.com']);
        $settings->set('orders.enabled_payment_methods', ['cod', 'bkash']);
        $settings->set('commerce.tax_enabled', true);

        $this->assertSame('09613828201', $settings->get('contact.phone'));
        $this->assertSame(['cod', 'bkash'], $settings->get('orders.enabled_payment_methods'));
        $this->assertTrue($settings->get('commerce.tax_enabled'));
        $this->assertSame('GD', $settings->group('orders')['number_prefix']);
    }

    public function test_settings_are_read_with_one_query_and_cached(): void
    {
        Setting::query()->create(['group' => 'general', 'key' => 'site_name', 'value' => 'Tech Store']);
        app()->forgetScopedInstances();

        DB::enableQueryLog();
        $first = app(SettingsService::class)->get('general.site_name');
        app()->forgetScopedInstances();
        $second = app(SettingsService::class)->get('general.site_name');
        $queries = collect(DB::getQueryLog())->pluck('query')->filter(fn (string $sql): bool => str_contains($sql, 'from `settings`'));

        $this->assertSame('Tech Store', $first);
        $this->assertSame('Tech Store', $second);
        $this->assertCount(1, $queries);
    }

    public function test_saving_a_setting_invalidates_the_cache(): void
    {
        $settings = app(SettingsService::class);
        $this->assertSame('Gadget Demo', $settings->get('general.site_name'));

        Setting::query()->create(['group' => 'general', 'key' => 'site_name', 'value' => 'Renamed']);

        $this->assertSame('Renamed', app(SettingsService::class)->get('general.site_name'));
    }

    public function test_keys_must_include_a_group(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(SettingsService::class)->set('site_name', 'x');
    }
}
