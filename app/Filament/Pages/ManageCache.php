<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\CatalogCache;
use App\Services\SettingsService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class ManageCache extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Cache Manager';

    protected static ?string $title = 'Cache & Performance Manager';

    protected static ?int $navigationSort = 99;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';

    protected string $view = 'filament.pages.manage-cache';

    public function flushCatalogCache(): void
    {
        app(CatalogCache::class)->flush();

        Notification::make()
            ->title('Catalog Cache Flushed')
            ->body('Home sections, categories, and product card caches have been cleared.')
            ->success()
            ->send();
    }

    public function flushSettingsCache(): void
    {
        app(SettingsService::class)->flush();

        Notification::make()
            ->title('Settings Cache Flushed')
            ->body('Store settings and branding configurations have been reloaded.')
            ->success()
            ->send();
    }

    public function clearViewCache(): void
    {
        Artisan::call('view:clear');

        Notification::make()
            ->title('Blade View Cache Cleared')
            ->body('Compiled storefront and admin view templates have been flushed.')
            ->success()
            ->send();
    }

    public function clearRouteConfigCache(): void
    {
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        Notification::make()
            ->title('Route & Config Cache Cleared')
            ->body('Routing table and configuration settings have been reset.')
            ->success()
            ->send();
    }

    public function clearAllCache(): void
    {
        app(CatalogCache::class)->flush();
        app(SettingsService::class)->flush();
        Artisan::call('optimize:clear');
        Cache::flush();

        Notification::make()
            ->title('All Caches Flushed Successfully!')
            ->body('System, catalog, view, route, and application caches are completely clean.')
            ->success()
            ->send();
    }
}
