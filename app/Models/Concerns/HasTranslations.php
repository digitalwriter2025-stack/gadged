<?php

namespace App\Models\Concerns;

use App\Support\Localization;
use Illuminate\Database\Eloquent\Model;

/**
 * Reads admin-entered translations kept in "{column}_{locale}" columns
 * (name_bn next to name). The plain column is the English text and the
 * fallback whenever a translation has not been written yet.
 *
 * @mixin Model
 */
trait HasTranslations
{
    /**
     * $attribute in the storefront language, or the English value when that translation is empty.
     */
    public function translated(string $attribute, ?string $locale = null): mixed
    {
        $locale ??= Localization::current();

        if (! Localization::isBase($locale)) {
            $translation = $this->getAttribute(Localization::column($attribute, $locale));

            if (filled($translation)) {
                return $translation;
            }
        }

        return $this->getAttribute($attribute);
    }
}
