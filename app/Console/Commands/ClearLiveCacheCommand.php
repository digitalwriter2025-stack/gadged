<?php

namespace App\Console\Commands;

use Dotenv\Dotenv;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Clears and rebuilds the live site's caches from this machine, for hosting
 * without SSH. Calls the token-protected /clear-cache endpoint using APP_URL and
 * DEPLOY_TOKEN from .env.production unless --url / --token are given.
 */
#[Signature('hosting:clear-cache
    {--url= : Live site URL (default: APP_URL in .env.production)}
    {--token= : Deploy token (default: DEPLOY_TOKEN in .env.production)}')]
#[Description('Clear and rebuild the live site caches without SSH (calls /clear-cache)')]
class ClearLiveCacheCommand extends Command
{
    public function handle(): int
    {
        $environment = $this->productionEnvironment();
        $url = rtrim((string) ($this->option('url') ?: ($environment['APP_URL'] ?? '')), '/');
        $token = (string) ($this->option('token') ?: ($environment['DEPLOY_TOKEN'] ?? ''));

        if ($url === '' || $token === '') {
            $this->components->error('Set APP_URL and DEPLOY_TOKEN in .env.production, or pass --url and --token.');

            return self::FAILURE;
        }

        try {
            $response = Http::acceptJson()->timeout(120)->post($url.'/clear-cache', ['token' => $token]);
        } catch (ConnectionException $exception) {
            $this->components->error("Could not reach {$url}: {$exception->getMessage()}");

            return self::FAILURE;
        }

        if ($response->status() === 403) {
            $this->components->error('The live site rejected the token. Check DEPLOY_TOKEN in the live .env file.');

            return self::FAILURE;
        }

        if (! $response->successful() || $response->json('status') !== 'success') {
            $this->components->error("Clearing the cache failed (HTTP {$response->status()}).");

            return self::FAILURE;
        }

        foreach ((array) $response->json('details', []) as $step => $output) {
            $this->components->twoColumnDetail(str_replace('_', ':', (string) $step), '<fg=green;options=bold>DONE</>');
        }

        $this->components->info("Live cache cleared and rebuilt on {$url}.");

        return self::SUCCESS;
    }

    /**
     * @return array<string, ?string>
     */
    private function productionEnvironment(): array
    {
        $path = base_path('.env.production');

        return is_file($path) ? Dotenv::parse((string) file_get_contents($path)) : [];
    }
}
