<?php

namespace App\Services;

use App\Support\Localization;
use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * Versioned cache for storefront data that changes only through the admin
 * (navigation, categories, brands, banners, homepage layout, menus, pages).
 *
 * flush() bumps the version, so every key is invalidated at once without cache
 * tags — this works on the file, database and Redis stores alike. Never store
 * stock, prices-in-cart, orders or customer data here.
 *
 * Keys are kept per storefront language, so menus and section titles cached
 * for a Bangla visitor are never served to an English one.
 */
class CatalogCache
{
    private const VERSION_KEY = 'catalog.version';

    public function remember(string $key, int $seconds, Closure $callback): mixed
    {
        return $this->store()->remember($this->key($key), $seconds, $callback);
    }

    public function rememberForever(string $key, Closure $callback): mixed
    {
        return $this->store()->rememberForever($this->key($key), $callback);
    }

    public function forget(string $key): void
    {
        foreach (array_keys(Localization::locales()) as $locale) {
            $this->store()->forget($this->key($key, $locale));
        }
    }

    public function flush(): void
    {
        $this->store()->forever(self::VERSION_KEY, $this->version() + 1);
    }

    public function version(): int
    {
        return (int) $this->store()->get(self::VERSION_KEY, 1);
    }

    private function key(string $key, ?string $locale = null): string
    {
        return 'catalog.v'.$this->version().'.'.($locale ?? Localization::current()).'.'.$key;
    }

    private function store(): Repository
    {
        return Cache::store(config('shop.cache.store'));
    }
}
