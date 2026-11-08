<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Container\Attributes\Scoped;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

/**
 * Cached access to grouped settings ("contact.phone"). All rows are loaded in one
 * query, cached until a Setting changes, and merged over config('shop.settings').
 */
#[Scoped]
class SettingsService
{
    private const CACHE_KEY = 'shop.settings.v1';

    /** @var array<string, array<string, mixed>>|null */
    private ?array $resolved = null;

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->all(), $key, $default);
    }

    /**
     * @return array<string, mixed>
     */
    public function group(string $group): array
    {
        return $this->all()[$group] ?? [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->resolved ??= array_replace_recursive(
            config('shop.settings', []),
            Cache::rememberForever(self::CACHE_KEY, fn (): array => $this->loadFromDatabase()),
        );
    }

    public function set(string $key, mixed $value): void
    {
        [$group, $name] = $this->split($key);

        Setting::query()->updateOrCreate(['group' => $group, 'key' => $name], ['value' => $value]);
    }

    /**
     * @param  array<string, mixed>  $values  keys without the group prefix
     */
    public function setMany(string $group, array $values): void
    {
        foreach ($values as $name => $value) {
            $this->set("{$group}.{$name}", $value);
        }
    }

    public function flush(): void
    {
        $this->resolved = null;
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function loadFromDatabase(): array
    {
        // Allows artisan commands to boot before the settings table exists.
        if (! Schema::hasTable('settings')) {
            return [];
        }

        $settings = [];

        foreach (Setting::query()->get(['group', 'key', 'value']) as $setting) {
            $settings[$setting->group][$setting->key] = $setting->value;
        }

        return $settings;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function split(string $key): array
    {
        $parts = explode('.', $key, 2);

        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            throw new InvalidArgumentException("Setting key [{$key}] must be in the form group.key.");
        }

        return [$parts[0], $parts[1]];
    }
}
