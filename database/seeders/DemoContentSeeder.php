<?php

namespace Database\Seeders;

use App\Enums\BannerPlacement;
use App\Enums\HomeSectionType;
use App\Enums\ProductCollectionSource;
use App\Models\Banner;
use App\Models\Category;
use App\Models\HomeSection;
use App\Models\Page;
use App\Models\Product;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Demo storefront content for local development: the homepage layout of the
 * reference design, contact/social/footer settings, published policy pages and
 * the bundled artwork in public/images, imported into the media library so
 * every image can be replaced from the admin panel.
 *
 * Runs once (skipped when a showcase section exists). Images are only imported
 * when $withMedia is true, which keeps the test suite free of file copies.
 */
class DemoContentSeeder extends Seeder
{
    /**
     * Product slug => bundled image under public/images/products.
     *
     * @var array<string, string>
     */
    private const PRODUCT_IMAGES = [
        'redragon-h320-lamia-rgb-virtual-71-surround-sound-gaming-headset' => 'redragon-headphone-headsets-h320-lamia.webp',
        'redragon-h130-pelias-wired-gaming-headphone' => 'redragon-headphone-headsets-h130.webp',
        'redragon-h231-scream-wired-rgb-gaming-headphone-white' => 'redragon-headphone-headsets-h231-scream.webp',
        'jbl-partybox-720-800w-bluetooth-party-speaker' => 'jbl-bluetooth-speaker-partybox-720.jpg',
        'kospet-tank-t3-ultra-2-143-inch-amoled-gps-bt-calling-smartwatch' => 'kospet-smart-watch-tank-t3-ultra-2.jpg',
        'apple-iphone-17-pro-256gb-cosmic-orange' => 'apple-mobile-iphone-17-pro.webp',
        'samsung-galaxy-s26-ultra-12gb256gb-cobalt-violet' => 'samsung-mobile-galaxy-s26-ultra.webp',
        'apple-iphone-17-256gb-lavender' => 'apple-mobile-iphone-17.webp',
        'google-pixel-9-pro-16gb256gb-obsidian' => 'google-mobile-pixel-9-pro.webp',
        'honor-x7d-8gb256gb-smartphone' => 'honor-mobile-x7d.webp',
        'apple-watch-series-11-gps-46mm-aluminum-case-with-sport-band' => 'apple-smart-watch-series-11.webp',
        'amazfit-active-2-square-amoled-gps-smart-watch' => 'amazfit-smart-watch-active-2-square.webp',
        'cmf-by-nothing-watch-pro-2-amoled-bluetooth-calling-smart-watch' => 'cmf-smart-watch-pro-2.webp',
        'haylou-solar-pro-amoled-bluetooth-calling-smart-watch' => 'haylou-smart-watch-solar-pro.webp',
        'kieslect-ks3-amoled-bluetooth-calling-smart-watch' => 'kieslect-smart-watch-ks3.webp',
        'apple-airpods-4-wireless-earbuds-with-usb-c-charging-case' => 'apple-earbuds-airpods-4.webp',
        'soundpeats-life-anc-wireless-earbuds' => 'soundpeats-earbuds-life-anc.webp',
        'xiaomi-redmi-buds-6-anc-tws-earbuds' => 'xiaomi-earbuds-redmi-buds-6.webp',
        'anker-soundcore-r50i-nc-wireless-earbuds' => 'anker-earbuds-soundcore-r50i-nc.webp',
        'qcy-ailybuds-pro-anc-wireless-earbuds' => 'qcy-earbuds-ailybuds-pro-plus.webp',
        'compact-ivory-10000mah-20w-fast-charging-power-bank' => 'power-bank-compact-ivory-10000mah.webp',
        'premium-brushed-silver-20000mah-65w-pd-power-bank' => 'power-bank-brushed-silver-20000mah.webp',
        'premium-graphite-30000mah-140w-laptop-power-bank' => 'power-bank-graphite-laptop-30000mah.webp',
        'premium-forest-green-20000mah-30w-power-bank' => 'power-bank-forest-green-20000mah.webp',
        'warm-white-5000mah-magnetic-wireless-power-bank' => 'power-bank-warm-white-magnetic-5000mah.webp',
        'slim-matte-charcoal-10000mah-20w-power-bank' => 'power-bank-slim-charcoal-10000mah.webp',
        'deep-navy-20000mah-225w-fast-charging-power-bank' => 'power-bank-deep-navy-20000mah.webp',
        'premium-black-40000mah-100w-high-capacity-power-bank' => 'power-bank-black-high-capacity-40000mah.webp',
        'premium-space-gray-25000mah-100w-laptop-power-bank' => 'power-bank-space-gray-laptop-25000mah.webp',
        'lavender-10000mah-magnetic-wireless-power-bank' => 'power-bank-lavender-magnetic-10000mah.webp',
        'braided-usb-c-to-usb-c-60w-fast-charging-cable-1m' => 'charger-cable-braided-usb-c-to-usb-c-60w.webp',
        'brushed-silver-usb-c-to-usb-a-30-otg-adapter' => 'charger-cable-usb-c-otg-adapter-silver.webp',
        'white-usb-a-to-usb-c-3a-fast-charging-cable-1m' => 'charger-cable-usb-a-to-usb-c-white.webp',
        'compact-black-38w-dual-port-car-charger' => 'charger-cable-car-charger-dual-port-38w.webp',
        'matte-black-usb-a-to-micro-usb-24a-charging-cable-1m' => 'charger-cable-usb-a-to-micro-usb-black.webp',
        'matte-charcoal-35w-dual-port-wall-charger' => 'charger-cable-wall-charger-dual-port-35w.webp',
        'satin-white-20w-usb-c-pd-wall-charger' => 'charger-cable-wall-charger-usb-c-20w.webp',
        'slim-15w-magnetic-wireless-charging-pad' => 'charger-cable-magnetic-wireless-charging-pad-15w.webp',
        'space-gray-5-in-1-usb-c-hub-with-4k-hdmi' => 'charger-cable-usb-c-hub-5-in-1.webp',
        'white-usb-a-to-lightning-24a-charging-cable-1m' => 'charger-cable-usb-a-to-lightning-white.webp',
        'charcoal-10w-portable-bluetooth-speaker' => 'speaker-portable-charcoal-10w.webp',
        'elegant-cream-retro-15w-bluetooth-speaker' => 'speaker-retro-cream-15w.webp',
        'rugged-navy-20w-waterproof-portable-bluetooth-speaker' => 'speaker-rugged-navy-20w.webp',
        'slim-black-40w-bluetooth-soundbar' => 'speaker-soundbar-slim-black-40w.webp',
        'premium-silver-gray-20w-smart-speaker-with-voice-assistant' => 'speaker-smart-silver-gray-20w.webp',
        'matte-black-25w-rgb-gaming-bluetooth-speaker' => 'speaker-gaming-rgb-matte-black-25w.webp',
        'portable-80w-party-speaker-with-blue-led-rings' => 'speaker-party-blue-led-80w.webp',
        'premium-olive-40w-portable-bluetooth-boombox-speaker' => 'speaker-portable-olive-40w.webp',
        'walnut-50w-bluetooth-bookshelf-speaker-with-copper-woofer' => 'speaker-bookshelf-walnut-copper-50w.webp',
        'samsung-galaxy-a56-5g-smartphone' => 'samsung-mobile-galaxy-a56.webp',
        'iphone-17-essentials-combo-iphone-17-airpods-4-20w-charger' => 'combo-iphone-17-essentials.webp',
        'galaxy-power-combo-galaxy-s26-ultra-20000mah-65w-power-bank-usb-c-cable' => 'combo-galaxy-power.webp',
        'travel-charging-combo-10000mah-power-bank-35w-wall-charger-car-charger-cable' => 'combo-travel-charging.webp',
        'fitness-combo-amazfit-active-2-smart-watch-soundpeats-life-anc-earbuds' => 'combo-fitness.webp',
        'desk-setup-combo-5-in-1-usb-c-hub-wireless-charging-pad-braided-cable' => 'combo-desk-setup.webp',
    ];

    /**
     * Featured category slug => icon under public/images/categories.
     *
     * @var list<string>
     */
    private const CATEGORY_IMAGES = [
        'smart-watch', 'headphones', 'earbuds', 'over-ear-headphones', 'speaker', 'powerbanks', 'charger-cable', 'smart-phone',
    ];

    /**
     * [placement, image, alt, link path (default: none)]
     *
     * @var list<array{0: BannerPlacement, 1: string, 2: string, 3?: string}>
     */
    private const BANNERS = [
        [BannerPlacement::HeroMain, 'hero/slide-1.webp', 'Smartphone mega sale — save up to 25%', 'category/smart-phone'],
        [BannerPlacement::HeroMain, 'hero/slide-5.webp', 'Smart living essentials', 'trending'],
        [BannerPlacement::HeroSideTop, 'banners/power-bank-offers.webp', 'Power bank offers: power for every day', 'category/powerbanks'],
        [BannerPlacement::HeroSideBottom, 'banners/combo-deals.webp', 'Combo deals: your everyday tech in one bundle', 'combos'],
        [BannerPlacement::Promo, 'banners/messenger-support.webp', 'Message us for product, stock, price and delivery info'],
    ];

    private const CHARGING_SHOWCASE = [
        'premium-graphite-30000mah-140w-laptop-power-bank',
        'premium-black-40000mah-100w-high-capacity-power-bank',
        'lavender-10000mah-magnetic-wireless-power-bank',
        'premium-brushed-silver-20000mah-65w-pd-power-bank',
        'matte-charcoal-35w-dual-port-wall-charger',
        'slim-15w-magnetic-wireless-charging-pad',
        'compact-black-38w-dual-port-car-charger',
        'space-gray-5-in-1-usb-c-hub-with-4k-hdmi',
    ];

    private const NEW_SHOWCASE = [
        'jbl-partybox-720-800w-bluetooth-party-speaker',
        'kospet-tank-t3-ultra-2-143-inch-amoled-gps-bt-calling-smartwatch',
        'apple-iphone-17-pro-256gb-cosmic-orange',
        'apple-airpods-4-wireless-earbuds-with-usb-c-charging-case',
        'apple-watch-series-11-gps-46mm-aluminum-case-with-sport-band',
        'samsung-galaxy-s26-ultra-12gb256gb-cobalt-violet',
        'warm-white-5000mah-magnetic-wireless-power-bank',
        'redragon-h320-lamia-rgb-virtual-71-surround-sound-gaming-headset',
    ];

    public function run(SettingsService $settings, ?bool $withMedia = null): void
    {
        $withMedia ??= app()->environment('local');

        if (! HomeSection::query()->where('type', HomeSectionType::Showcase)->exists()) {
            $this->seedHomeLayout($withMedia);
            $this->seedSettings($settings, $withMedia);
            $this->publishPages();

            if ($withMedia) {
                $this->seedBanners();
            }
        }

        // Only fills products and categories still missing an image, so products
        // added to the catalog later get theirs on the next run.
        if ($withMedia) {
            $this->attachCatalogImages();
        }
    }

    private function seedHomeLayout(bool $withMedia): void
    {
        $categoryId = fn (string $slug): ?int => Category::query()->where('slug', $slug)->value('id');
        $categoryUrl = fn (string $slug): string => route('store.categories.show', $slug, absolute: false);

        HomeSection::query()->delete();

        // [type, title, subtitle, settings, is_active, manual product slugs]
        $sections = [
            [HomeSectionType::Hero, null, null, [], true],
            [HomeSectionType::ServiceHighlights, null, null, [], false],
            [HomeSectionType::FeaturedCategories, 'Featured Categories', 'Explore our wide range of gadgets', ['limit' => 16], true],
            [HomeSectionType::FlashSale, 'Flash Sale', 'Limited time deals', ['limit' => 12], true],
            [HomeSectionType::ProductCollection, 'Trending Now', 'The gadgets everyone is talking about this week', ['source' => ProductCollectionSource::Trending->value, 'limit' => 10], true],
            [HomeSectionType::ProductCollection, 'Mobile Phones', 'Latest flagship and everyday smartphones from top brands', ['source' => ProductCollectionSource::Category->value, 'category_id' => $categoryId('smart-phone'), 'limit' => 10], true],
            [HomeSectionType::ProductCollection, 'Smart Watches', 'Track your fitness and stay connected on the go', ['source' => ProductCollectionSource::Category->value, 'category_id' => $categoryId('smart-watch'), 'limit' => 10], true],
            [HomeSectionType::ProductCollection, 'Earbuds', 'True wireless sound for music, calls and gaming', ['source' => ProductCollectionSource::Category->value, 'category_id' => $categoryId('earbuds'), 'limit' => 10], true],
            [HomeSectionType::Showcase, 'Stay charged on the go with power banks and chargers from ৳199', null, ['theme' => 'midnight', 'source' => ProductCollectionSource::Manual->value, 'cta_label' => 'Shop all', 'view_all_url' => $categoryUrl('powerbanks'), 'limit' => 8], true, self::CHARGING_SHOWCASE],
            [HomeSectionType::ProductCollection, 'Combo Deals', 'Bundle up and save more on gadgets that go together', ['source' => ProductCollectionSource::Combos->value, 'limit' => 10], true],
            [HomeSectionType::ProductCollection, 'Featured Products', 'Discover our handpicked selection of premium tech products', ['source' => ProductCollectionSource::Featured->value, 'limit' => 12], true],
            [HomeSectionType::Showcase, 'New and Noteworthy', 'Explore the latest finds from innovative tech and trending product releases.', ['theme' => 'aurora', 'source' => ProductCollectionSource::Manual->value, 'cta_label' => 'Shop now', 'view_all_url' => route('store.trending', absolute: false), 'limit' => 8], true, self::NEW_SHOWCASE],
            [HomeSectionType::ProductCollection, 'Latest Products', 'Check out our newest arrivals', ['source' => ProductCollectionSource::Latest->value, 'limit' => 12], true],
            [HomeSectionType::PromoBanners, null, null, [], true],
            [HomeSectionType::ProductCollection, 'Best Sellers', 'Top-rated products loved by our customers', ['source' => ProductCollectionSource::BestSellers->value, 'limit' => 16], true],
            [HomeSectionType::ProductGrid, 'All Product', 'Browse our complete collection', ['source' => ProductCollectionSource::Latest->value, 'limit' => 60, 'initial_count' => 5], true],
        ];

        foreach ($sections as $order => $definition) {
            [$type, $title, $subtitle, $sectionSettings, $isActive] = $definition;

            $section = HomeSection::query()->create([
                'type' => $type,
                'title' => $title,
                'subtitle' => $subtitle,
                'settings' => $sectionSettings,
                'is_active' => $isActive,
                'sort_order' => $order + 1,
            ]);

            $slugs = $definition[5] ?? [];

            foreach (Product::query()->whereIn('slug', $slugs)->pluck('id', 'slug') as $slug => $productId) {
                $section->items()->create([
                    'itemable_type' => (new Product)->getMorphClass(),
                    'itemable_id' => $productId,
                    'sort_order' => array_search($slug, $slugs, true) + 1,
                ]);
            }

            if ($withMedia && $type === HomeSectionType::Showcase && $sectionSettings['theme'] === 'midnight') {
                foreach (['power-bank-graphite-laptop-30000mah.webp', 'charger-cable-wall-charger-dual-port-35w.webp'] as $image) {
                    $section->addMedia(public_path("images/products/{$image}"))->preservingOriginal()->toMediaCollection('images');
                }
            }
        }
    }

    private function seedSettings(SettingsService $settings, bool $withMedia): void
    {
        $settings->set('general.site_name', 'Tech Craze BD');

        $settings->setMany('contact', [
            'hotline' => '+8801711727860',
            'email' => 'techcrazebd@gmail.com',
            'whatsapp' => '8801711727860',
            'address' => 'Head Office: Your address here, Dhaka',
            'map_url' => 'https://maps.google.com/?q=Dhaka',
            'complain_url' => 'mailto:techcrazebd@gmail.com',
        ]);

        $settings->setMany('social', [
            'facebook' => 'https://www.facebook.com/',
            'messenger' => 'https://m.me/',
            'youtube' => 'https://www.youtube.com/',
            'instagram' => 'https://www.instagram.com/',
            'linkedin' => 'https://www.linkedin.com/',
        ]);

        $settings->setMany('footer', [
            'developer_name' => 'Digital Webars',
            'developer_url' => 'https://www.digitalwebars.com/',
        ]);

        if (! $withMedia) {
            return;
        }

        $settings->set('general.logo', $this->copyToPublicDisk('brand/tcb-logo-horizontal.png', 'settings'));
        $settings->set('general.favicon', $this->copyToPublicDisk('brand/tcb-favicon.png', 'settings'));
        $settings->set('footer.payment_methods_image', $this->copyToPublicDisk('payments/payment-methods.webp', 'settings'));
        $settings->set('footer.affiliations', array_map(
            fn (array $logo): array => ['image' => $this->copyToPublicDisk("affiliations/{$logo[0]}", 'settings/affiliations'), 'name' => $logo[1], 'url' => null],
            [['basis.png', 'BASIS'], ['bcs.png', 'BCS'], ['ecs.png', 'ECS'], ['ecab.png', 'e-CAB']],
        ));
    }

    /**
     * Footer policy pages are created empty and hidden; give them placeholder text so the links work.
     */
    private function publishPages(): void
    {
        Page::query()->whereNull('content')->each(fn (Page $page) => $page->update([
            'content' => "<p>This is the {$page->title} page. Replace this text from Content → Pages in the admin panel.</p>",
            'is_active' => true,
        ]));
    }

    private function seedBanners(): void
    {
        foreach (self::BANNERS as $order => $definition) {
            [$placement, $image, $alt] = $definition;

            $banner = Banner::query()->create([
                'placement' => $placement,
                'title' => $alt,
                'alt_text' => $alt,
                'url' => isset($definition[3]) ? '/'.$definition[3] : '#',
                'sort_order' => $order + 1,
            ]);

            $banner->addMedia(public_path("images/{$image}"))->preservingOriginal()->toMediaCollection('desktop');
        }
    }

    /**
     * Images not yet added under public/images are skipped, so they attach on a later run.
     */
    private function attachCatalogImages(): void
    {
        $productImages = array_filter(self::PRODUCT_IMAGES, fn (string $image): bool => file_exists(public_path("images/products/{$image}")));
        $categoryImages = array_filter(self::CATEGORY_IMAGES, fn (string $slug): bool => file_exists(public_path("images/categories/{$slug}.png")));

        Product::query()
            ->whereIn('slug', array_keys($productImages))
            ->whereDoesntHave('media', fn ($media) => $media->where('collection_name', 'main'))
            ->each(fn (Product $product) => $product
                ->addMedia(public_path('images/products/'.$productImages[$product->slug]))
                ->preservingOriginal()
                ->toMediaCollection('main'));

        Category::query()
            ->whereIn('slug', $categoryImages)
            ->whereDoesntHave('media')
            ->each(fn (Category $category) => $category
                ->addMedia(public_path("images/categories/{$category->slug}.png"))
                ->preservingOriginal()
                ->toMediaCollection('image'));
    }

    /**
     * Copies a bundled image to the public disk, where admin uploads live, and returns its path there.
     */
    private function copyToPublicDisk(string $source, string $directory): string
    {
        $path = $directory.'/'.basename($source);

        Storage::disk('public')->put($path, file_get_contents(public_path("images/{$source}")));

        return $path;
    }
}
