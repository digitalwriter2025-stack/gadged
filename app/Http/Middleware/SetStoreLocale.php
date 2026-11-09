<?php

namespace App\Http\Middleware;

use App\Services\SettingsService;
use App\Support\Localization;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Storefront language: the visitor's saved choice, otherwise the default
 * language set in the admin. The admin panel is not covered and stays English.
 */
class SetStoreLocale
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale($this->resolve($request));

        return $next($request);
    }

    private function resolve(Request $request): string
    {
        $chosen = $request->cookie(Localization::COOKIE);

        if (Localization::isSupported($chosen)) {
            return $chosen;
        }

        $default = $this->settings->get('localization.default_locale');

        return Localization::isSupported($default) ? $default : Localization::BASE_LOCALE;
    }
}
