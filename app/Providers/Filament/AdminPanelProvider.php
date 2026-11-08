<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\ManageCache;
use App\Services\SettingsService;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Filament\Auth\Pages\PasswordReset\ResetPassword;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->passwordReset()
            ->profile()
            ->userMenuItems([
                MenuItem::make()
                    ->label('Cache Clean')
                    ->url(fn (): string => ManageCache::getUrl())
                    ->icon('heroicon-o-arrow-path'),
            ])
            // Panel-wide theme (sidebar, cards, tables, dashboard banner) as plain CSS, so no Vite build is needed.
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn (): View => view('filament.hooks.panel-styles'),
            )
            // Branded split-screen layout for the guest auth pages.
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn (): View => view('filament.hooks.auth-styles'),
                scopes: [Login::class, RequestPasswordReset::class, ResetPassword::class],
            )
            ->renderHook(
                PanelsRenderHook::SIMPLE_LAYOUT_START,
                fn (): View => view('filament.hooks.auth-brand-panel'),
                scopes: [Login::class, RequestPasswordReset::class, ResetPassword::class],
            )
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn (): View => view('filament.hooks.topbar-cache-clean')
            )
            // Staff can enrol an authenticator app from their profile; enforcement can be switched on later.
            ->multiFactorAuthentication([
                AppAuthentication::make()->recoverable(),
            ])
            ->brandName(fn (): string => (string) app(SettingsService::class)->get('general.site_name', config('app.name')))
            ->colors([
                'primary' => Color::hex('#df0100'),
                'gray' => Color::Slate,
            ])
            ->font('Poppins')
            ->maxContentWidth(Width::ScreenTwoExtraLarge)
            // SPA-style navigation: page changes are swapped in without full reloads.
            ->spa()
            ->unsavedChangesAlerts()
            ->databaseTransactions()
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make('Sales'),
                NavigationGroup::make('Catalog'),
                NavigationGroup::make('Inventory'),
                NavigationGroup::make('Marketing'),
                NavigationGroup::make('Customers'),
                NavigationGroup::make('Content'),
                NavigationGroup::make('Settings'),
                NavigationGroup::make('Access Control')->collapsed(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
