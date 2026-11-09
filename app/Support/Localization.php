<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Foundation\Application;

/**
 * Storefront languages (config('shop.locales')). English is the base language
 * kept in the plain columns and settings keys; every other language lives next
 * to it with a locale suffix: name → name_bn, announcement.text → announcement.text_bn.
 */
final class Localization
{
    public const BASE_LOCALE = 'en';

    /**
     * Cookie holding the visitor's chosen language.
     */
    public const COOKIE = 'locale';

    private const BANGLA_DIGITS = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

    /**
     * @return array<string, array{name: string, short: string}>
     */
    public static function locales(): array
    {
        return config('shop.locales', []);
    }

    public static function isSupported(mixed $locale): bool
    {
        return is_string($locale) && array_key_exists($locale, self::locales());
    }

    /**
     * The request's language; English when the framework is not booted (plain unit tests).
     */
    public static function current(): string
    {
        $app = app();

        return $app instanceof Application ? $app->getLocale() : self::BASE_LOCALE;
    }

    public static function isBase(?string $locale = null): bool
    {
        return ($locale ?? self::current()) === self::BASE_LOCALE;
    }

    /**
     * Column (or settings key) that holds $column in $locale: "name" stays "name"
     * in English and becomes "name_bn" in Bangla.
     */
    public static function column(string $column, ?string $locale = null): string
    {
        $locale ??= self::current();

        return self::isBase($locale) ? $column : "{$column}_{$locale}";
    }

    /**
     * Text built by the storefront JavaScript, keyed by its English wording
     * (read by resources/js/modules/i18n.js).
     *
     * @return array<string, string>
     */
    public static function scriptStrings(): array
    {
        $strings = [
            'Select delivery method', 'Free', 'Products', 'Categories', 'Recent searches', 'Clear',
            'No results for “:term”', 'Check the spelling or try a more general term.',
            'View all :count result', 'View all :count results',
        ];

        return self::isBase() ? [] : array_combine($strings, array_map(fn (string $text): string => __($text), $strings));
    }

    /**
     * A date with month and day names, and digits, in the current language.
     */
    public static function date(CarbonInterface $date, string $format): string
    {
        return self::digits($date->translatedFormat($format));
    }

    /**
     * Writes Western digits in the current language's numerals (১২,৫০০ in Bangla).
     */
    public static function digits(string|int|float|null $value): string
    {
        $value = (string) $value;

        return self::current() === 'bn'
            ? str_replace(['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], self::BANGLA_DIGITS, $value)
            : $value;
    }
}
