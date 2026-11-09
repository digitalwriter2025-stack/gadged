/**
 * Storefront translations for text built in JavaScript. The page embeds the
 * strings for the current language in <script id="store-i18n">, keyed by the
 * English text (see App\Support\Localization::scriptStrings()).
 */
const strings = (() => {
    try {
        return JSON.parse(document.getElementById('store-i18n')?.textContent || '{}');
    } catch {
        return {};
    }
})();

export const locale = document.body?.dataset.locale || 'en';

const BANGLA_DIGITS = '০১২৩৪৫৬৭৮৯';

/**
 * Western digits in the current language's numerals ("12" → "১২" in Bangla).
 */
export function digits(value) {
    const text = String(value);

    return locale === 'bn' ? text.replace(/[0-9]/g, (digit) => BANGLA_DIGITS[digit]) : text;
}

/**
 * Translate `text`, then fill `:name` placeholders.
 */
export function t(text, replacements = {}) {
    return Object.entries(replacements).reduce(
        (translated, [key, value]) => translated.replaceAll(`:${key}`, String(value)),
        strings[text] ?? text,
    );
}
