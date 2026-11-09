<?php

declare(strict_types=1);

namespace App\Support\Filament;

use App\Support\Localization;
use Closure;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

/**
 * English / বাংলা tabs for the customer-facing text in an admin form.
 *
 * The English tab edits the plain columns (name), the বাংলা tab the
 * "_bn" columns (name_bn). Bangla is optional: wherever it is left empty the
 * storefront shows the English text.
 */
final class TranslatableTabs
{
    /**
     * @param  string  $name  unique within the form, e.g. "details" or "faqs"
     * @param  Closure(string $suffix, bool $isEnglish): list<Component>  $fields  builds one tab; called once per language with "" or "_bn"
     */
    public static function make(string $name, Closure $fields): Tabs
    {
        $tabs = [];

        foreach (Localization::locales() as $locale => $language) {
            $isEnglish = Localization::isBase($locale);

            $tabs[] = Tab::make($language['name'])
                ->key("{$name}-{$locale}", isInheritable: false)
                ->icon($isEnglish ? 'heroicon-o-language' : 'heroicon-o-sparkles')
                ->badge($isEnglish ? null : 'Optional')
                ->badgeColor('gray')
                ->schema($fields($isEnglish ? '' : "_{$locale}", $isEnglish));
        }

        return Tabs::make('Language')
            ->key("{$name}-languages")
            ->tabs($tabs)
            ->extraAttributes(['class' => 'gx-language-tabs']);
    }

    /**
     * Placeholder hint for a Bangla field: the English text it falls back to.
     */
    public static function fallbackHint(bool $isEnglish): ?string
    {
        return $isEnglish ? null : 'Leave empty to show the English text.';
    }
}
