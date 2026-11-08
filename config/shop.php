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
        'contact' => [
            'phone' => null,
            'hotline' => null,
            'email' => null,
            'whatsapp' => null,
            'address' => null,
            'map_url' => null,
            'complain_url' => null,
            'office_hours' => null,
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
            'meta_description' => null,
            'og_image' => null,
            'robots_index' => true,
        ],
        'analytics' => [
            'google_tag_id' => null,
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
            'developer_name' => null,
            'developer_url' => null,
            'payment_methods_image' => null,
            // list of {image: public-disk path, name: string, url: ?string}
            'affiliations' => [],
        ],
    ],

];
