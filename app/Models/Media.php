<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;
use Spatie\MediaLibrary\Support\UrlGenerator\UrlGeneratorFactory;

/**
 * Media-library model with a faster conversion URL.
 *
 * The package rebuilds every conversion of the owning model on each getUrl()
 * call, which dominated listing pages with dozens of cards. Conversions here are
 * defined per model class (HasImageConversions), so they are resolved once per
 * owner type and reused. Media with stored manipulations, or owners whose
 * conversions depend on the model instance, keep the package behaviour.
 */
class Media extends BaseMedia
{
    /**
     * Conversions by owner morph type and name; null when the owner type cannot share them.
     *
     * @var array<string, array<string, Conversion>|null>
     */
    private static array $sharedConversions = [];

    public function getUrl(string $conversionName = ''): string
    {
        $conversion = $conversionName === '' ? null : $this->sharedConversion($conversionName);

        if ($conversion === null) {
            return parent::getUrl($conversionName);
        }

        return UrlGeneratorFactory::createForMedia($this)
            ->setConversion($conversion)
            ->getUrl();
    }

    private function sharedConversion(string $conversionName): ?Conversion
    {
        if ($this->manipulations !== []) {
            return null;
        }

        if (! array_key_exists($this->model_type, self::$sharedConversions)) {
            self::$sharedConversions[$this->model_type] = $this->ownerHasClassLevelConversions() ? [] : null;
        }

        if (self::$sharedConversions[$this->model_type] === null) {
            return null;
        }

        return self::$sharedConversions[$this->model_type][$conversionName]
            ??= ConversionCollection::createForMedia($this)->getByName($conversionName);
    }

    private function ownerHasClassLevelConversions(): bool
    {
        $ownerClass = Relation::getMorphedModel($this->model_type) ?? $this->model_type;

        if (! is_subclass_of($ownerClass, HasMedia::class)) {
            return false;
        }

        return ! (new $ownerClass)->registerMediaConversionsUsingModelInstance;
    }
}
