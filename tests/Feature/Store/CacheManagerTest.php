<?php

namespace Tests\Feature\Store;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CacheManagerTest extends TestCase
{
    private const TOKEN = 'test-deploy-token-0123456789abcdef';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.deploy_token', self::TOKEN);
    }

    public function test_wrong_token_is_rejected(): void
    {
        $this->postJson(route('clear-cache'), ['token' => 'wrong'])
            ->assertForbidden()
            ->assertJsonPath('status', 'error');
    }

    public function test_valid_token_clears_cache_without_a_csrf_token(): void
    {
        Artisan::shouldReceive('call')->times(5)->andReturn(0);
        Artisan::shouldReceive('output')->andReturn('OK');

        $this->postJson(route('clear-cache'), ['token' => self::TOKEN])
            ->assertOk()
            ->assertJsonPath('status', 'success');
    }

    public function test_command_clears_the_live_cache(): void
    {
        Http::fake(['shop.test/clear-cache' => Http::response(['status' => 'success', 'details' => ['optimize_clear' => 'OK']])]);

        $this->artisan('hosting:clear-cache', ['--url' => 'https://shop.test/', '--token' => self::TOKEN])
            ->expectsOutputToContain('Live cache cleared')
            ->assertSuccessful();

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://shop.test/clear-cache'
            && $request['token'] === self::TOKEN);
    }

    public function test_command_fails_when_the_live_site_rejects_the_token(): void
    {
        Http::fake(['shop.test/clear-cache' => Http::response(['status' => 'error'], 403)]);

        $this->artisan('hosting:clear-cache', ['--url' => 'https://shop.test', '--token' => 'wrong'])
            ->expectsOutputToContain('rejected the token')
            ->assertFailed();
    }
}
