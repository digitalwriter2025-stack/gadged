<?php

/*
|--------------------------------------------------------------------------
| Shop defaults
|--------------------------------------------------------------------------
|
| Fallback values for store settings. Anything editable by staff is stored in
| the `settings` table (group.key) and read through SettingsService, which
| falls back to the `settings` array below when a key has not been saved yet.
|
*/

return [

    'currency' => [
        'code' => env('SHOP_CURRENCY', 'BDT'),
        'symbol' => env('SHOP_CURRENCY_SYMBOL', '৳'),
    ],

    'inventory' => [
        'low_stock_threshold' => (int) env('SHOP_LOW_STOCK_THRESHOLD', 5),
    ],

    'cart' => [
        'cookie' => 'cart_token',
        'guest_lifetime_days' => 30,
        'max_quantity_per_line' => 20,
    ],

    'cache' => [
        'store' => env('SHOP_CACHE_STORE'),
    ],

    /*
    | Storefront languages, in switcher order. English is stored in the plain
    | columns; every other language in a "{column}_{locale}" column next to it.
    */
    'locales' => [
        'en' => ['name' => 'English', 'short' => 'EN'],
        'bn' => ['name' => 'বাংলা', 'short' => 'বাং'],
    ],

    /*
    | "EMI starts from" on the product page: price split over the longest tenure,
    | shown only for products at or above min_price.
    */
    'emi' => [
        'max_tenure_months' => (int) env('SHOP_EMI_MAX_TENURE_MONTHS', 36),
        'min_price' => env('SHOP_EMI_MIN_PRICE', '5000'),
    ],

    /*
    | Default values per settings group. Keys here define the settings schema.
    */
    'settings' => [
        'general' => [
            'site_name' => env('APP_NAME', 'Tech Craze BD'),
            'tagline' => null,
            'logo' => null,
            'logo_dark' => null,
            'favicon' => null,
        ],
        // Language a first-time visitor sees, and whether the header shows the English / বাংলা switch.
        'localization' => [
            'default_locale' => 'en',
            'show_switcher' => true,
        ],
        'contact' => [
            'phone' => null,
            'hotline' => null,
            'email' => null,
            'whatsapp' => null,
            'address' => null,
            'address_bn' => null,
            'map_url' => null,
            'complain_url' => null,
            'office_hours' => null,
        ],
        // Header quick links next to the cart; an empty label, caption or URL keeps the built-in one.
        'header' => [
            'show_pc_builder' => false,
            'track_enabled' => true,
            'track_label' => null,
            'track_label_bn' => null,
            'track_caption' => null,
            'track_caption_bn' => null,
            'track_url' => null,
            'combo_enabled' => true,
            'combo_label' => null,
            'combo_label_bn' => null,
            'combo_caption' => null,
            'combo_caption_bn' => null,
            'combo_url' => null,
        ],
        'social' => [
            'facebook' => null,
            'messenger' => null,
            'youtube' => null,
            'instagram' => null,
            'linkedin' => null,
            'tiktok' => null,
        ],
        'seo' => [
            'meta_title' => null,
            'meta_title_bn' => null,
            'meta_description' => null,
            'meta_description_bn' => null,
            'og_image' => null,
            'robots_index' => true,
        ],
        // Google tags, read through App\Services\GoogleTag.
        'analytics' => [
            'ga4_measurement_id' => null,
            'google_ads_id' => null,
            'google_ads_conversion_label' => null,
            'gtm_id' => null,
            // The single "G- or GTM-" field used before the IDs above; read only as a fallback.
            'google_tag_id' => null,
            'custom_head_scripts' => null,
            'custom_body_scripts' => null,
        ],
        // Meta Pixel + Conversions API, read through App\Services\MetaPixel.
        'meta_pixel' => [
            'pixel_id' => null,
            'browser_enabled' => true,
            'server_enabled' => false,
            // Encrypted with Crypt::encryptString().
            'access_token' => null,
            'test_event_code' => null,
            'advanced_matching' => false,
            // 'id' or 'sku'; must match the id column of the Commerce Manager catalogue feed.
            'content_id' => 'id',
            'api_version' => 'v21.0',
            'domain_verification' => null,
        ],
        // Storefront colours, printed as Tailwind theme variables (button_hover → --color-button-hover).
        // Keep in step with the @theme block in resources/css/app.css.
        'theme' => [
            'button' => '#df0100',
            'button_hover' => '#b30000',
            'button_text' => '#ffffff',
            'accent' => '#df0100',
            'accent_dark' => '#b30000',
            'builder' => '#1f1f1f',
            'price' => '#df0100',
            'savings' => '#0b6b3a',
            'topbar' => '#df0100',
            'surface' => '#f3f4f6',
            'footer' => '#121212',
            'footer_link' => '#ff5a52',
            'whatsapp' => '#22c35e',
        ],
        'commerce' => [
            'currency_code' => 'BDT',
            'currency_symbol' => '৳',
            'tax_enabled' => false,
            'tax_rate' => '0',
            'prices_include_tax' => true,
        ],
        'orders' => [
            'number_prefix' => 'GD',
            'guest_checkout' => true,
            'min_order_amount' => '0',
            'unpaid_online_expiry_minutes' => 60,
            'enabled_payment_methods' => ['cod'],
        ],
        'shipping' => [
            'default_method' => 'standard',
            'show_delivery_estimate' => true,
        ],
        'footer' => [
            'about_text' => null,
            'copyright_text' => null,
            'copyright_text_bn' => null,
            'developer_name' => null,
            'developer_url' => null,
            'payment_methods_image' => null,
            // list of {image: public-disk path, name: string, url: ?string}
            'affiliations' => [],
        ],
    ],

];
