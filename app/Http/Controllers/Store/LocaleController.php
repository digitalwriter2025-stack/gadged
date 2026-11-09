<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Support\Localization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    private const COOKIE_MINUTES = 60 * 24 * 365;

    /**
     * Save the visitor's language and reload the page they were on.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(array_keys(Localization::locales()))],
        ]);

        Cookie::queue(Localization::COOKIE, $validated['locale'], self::COOKIE_MINUTES);

        // Pages prefetched on hover in the old language would otherwise be reused for a few minutes.
        return back()->withHeaders(['Clear-Site-Data' => '"cache"']);
    }
}
