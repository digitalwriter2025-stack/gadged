<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Pages\ManageSiteSettings;
use App\Filament\Resources\Banners\BannerResource;
use App\Filament\Resources\FlashSales\FlashSaleResource;
use App\Filament\Resources\Products\ProductResource;
use Filament\Widgets\Widget;

/**
 * Greeting banner at the top of the dashboard with shortcuts the signed-in staff member can use.
 */
class WelcomeBanner extends Widget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.welcome-banner';

    public function getGreeting(): string
    {
        return match (true) {
            now(config('app.display_timezone'))->hour < 12 => 'Good morning',
            now(config('app.display_timezone'))->hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };
    }

    public function getToday(): string
    {
        return now(config('app.display_timezone'))->format('l, j F Y');
    }

    /**
     * @return list<array{label: string, icon: string, url: string, primary: bool}>
     */
    public function getQuickActions(): array
    {
        $actions = [
            [
                'visible' => ProductResource::canCreate(),
                'label' => 'Add product',
                'icon' => 'heroicon-m-plus',
                'url' => fn (): string => ProductResource::getUrl('create'),
                'primary' => true,
            ],
            [
                'visible' => ProductResource::canAccess(),
                'label' => 'Products',
                'icon' => 'heroicon-m-cube',
                'url' => fn (): string => ProductResource::getUrl(),
                'primary' => false,
            ],
            [
                'visible' => FlashSaleResource::canAccess(),
                'label' => 'Flash sales',
                'icon' => 'heroicon-m-bolt',
                'url' => fn (): string => FlashSaleResource::getUrl(),
                'primary' => false,
            ],
            [
                'visible' => BannerResource::canAccess(),
                'label' => 'Banners',
                'icon' => 'heroicon-m-photo',
                'url' => fn (): string => BannerResource::getUrl(),
                'primary' => false,
            ],
            [
                'visible' => ManageSiteSettings::canAccess(),
                'label' => 'Settings',
                'icon' => 'heroicon-m-cog-6-tooth',
                'url' => fn (): string => ManageSiteSettings::getUrl(),
                'primary' => false,
            ],
        ];

        return array_values(array_map(
            fn (array $action): array => [
                'label' => $action['label'],
                'icon' => $action['icon'],
                'url' => ($action['url'])(),
                'primary' => $action['primary'],
            ],
            array_filter($actions, fn (array $action): bool => $action['visible']),
        ));
    }
}
