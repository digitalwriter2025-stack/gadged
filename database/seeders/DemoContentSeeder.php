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
        'lenovo-g24-20-24-inch-fhd-144hz-ips-gaming-monitor' => 'lenovo-monitor-g24-20.webp',
        'lenovo-l24-4e-238-inch-100hz-full-hd-ips-monitor-black' => 'lenovo-monitor-l24-4e.webp',
        'lenovo-l24i-40-238-inch-ips-fhd-100hz-professional-monitor' => 'lenovo-monitor-l24i-40.webp',
        'lenovo-ideapad-slim-3-15irh10-core-i5-13th-gen-153-laptop' => 'lenovo-ideapad-slim-3-15irh10.jpg',
        'pny-geforce-rtx-5060-ti-8gb-epic-x-rgb-oc-graphics-card' => 'pny-graphics-card-geforce-rtx-5060-ti-8gb-epic-x-rgb-oc-triple-fan.png',
        'lg-34u640b-b-34-inch-wqhd-ultrawide-curved-monitor' => 'lg-monitor-34u640b-b.jpg',
        'asus-nuc-15-pro-core-5-210h-wi-fi-7-be202-mini-pc' => 'asus-all-in-one-nuc-15-pro.jpg',
        'jbl-partybox-720-800w-bluetooth-party-speaker' => 'jbl-bluetooth-speaker-partybox-720.jpg',
        'arzopa-z1fc-g1-game-max-161-inch-144hz-portable-gaming-monitor' => 'arzopa-monitor-z1fc.jpg',
        'kospet-tank-t3-ultra-2-143-inch-amoled-gps-bt-calling-smartwatch' => 'kospet-smart-watch-tank-t3-ultra-2.jpg',
        'amd-ryzen-7-7700-msi-pro-b650m-p-16gb-ram-500gb-ssd-rtx-5070-gaming-pc' => 'amd-gaming-pc-amd-ryzen-7-7700.jpg',
        'unika-radeon-rx-580-blizzards-8gd5-v2-graphics-card' => 'unika-graphics-card-rx-580-blizzards-8gd5-v2.jpg',
        'msi-pro-mp243l-e14-238-144hz-ips-fhd-gaming-monitor' => 'msi-monitor-pro-mp243l-e14.jpg',
        'pny-geforce-rtx-5060-8gb-oc-dual-fan-graphics-card' => 'pny-graphics-card-geforce-rtx-5060-8gb-oc-dual-fan.jpg',
        'msi-pro-mp251-e14l-245-inch-fhd-144hz-ips-gaming-monitor' => 'msi-monitor-pro-mp251-e14l.jpg',
        'maxcool-inferno-mc5-micro-atx-gaming-computer-case' => 'maxcool-computer-case-inferno-mc5.jpg',
        'amd-ryzen-7-7700-processor' => 'amd-processor-ryzen-7-7700.jpg',
        'haier-hsu-19gravityinv-pro-x6-15-ton-inverter-air-conditioner' => 'haier-air-conditioner-hsu-19gravity-inv-pro-x6.jpg',
        'samsung-55u8510-55-inch-crystal-uhd-4k-smart-tv' => 'samsung-television-55u8510.webp',
        'anker-solix-c1000-gen-2-portable-power-station' => 'anker-power-station-solix-c1000-gen-2.jpg',
        'gigabyte-geforce-rtx-5070-ti-windforce-oc-sff-16g-graphics-card' => 'gigabyte-graphics-card-geforce-rtx-5070-ti-windforce-oc-sff-16g.jpg',
        'lenovo-l24i-4a-238-inch-fhd-ips-100hz-monitor' => 'lenovo-monitor-l24i-4a.webp',
        'samsung-50u8000f-50-inch-crystal-uhd-4k-smart-tv' => 'samsung-television-50u8000f.webp',
        'tcl-32s5k-32-inch-hd-android-smart-tv' => 'tcl-television-32s5k.webp',
        'pny-geforce-rtx-5080-16gb-gddr7-argb-epic-x-rgb-oc-graphics-card' => 'pny-graphics-card-rtx-5080-16gb-gddr7-argb-epic-x-rgb-oc-triple-fan.jpg',
        'zotac-gaming-geforce-rtx-5080-amp-extreme-infinity-graphics-card' => 'zotac-graphics-card-gaming-geforce-rtx-5080-amp-extreme-infinity.jpg',
        'haier-hsu-19gravity-inv-pro-x6-15-ton-inverter-ac' => 'haier-air-conditioner-hsu-19gravity-inv-pro-x6.jpg',
        'amd-ryzen-7-9700x-gaming-pc' => 'amd-gaming-pc-amd-ryzen-7-9700x.jpg',
        'asus-tuf-gaming-vg249qm5f-238-inch-fhd-210hz-monitor' => 'asus-monitor-tuf-series-5-vg249qm5f.jpg',
        'asus-tuf-gaming-vg27aql5f-27-inch-qhd-210hz-monitor' => 'asus-monitor-tuf-series-5-vg27aql5f.jpg',
        'asus-va249qgs-w-238-inch-fhd-144hz-ips-monitor' => 'asus-monitor-va249qgs-w.jpg',
        'dahua-dhi-lm27-b221w-27-inch-fhd-monitor' => 'dahua-monitor-dhi-lm27-b221w.jpg',
        'msi-pro-mp225-e12vl-2145-inch-fhd-120hz-monitor' => 'msi-monitor-pro-mp225-e12vl.jpg',
        'pny-geforce-rtx-5070-ti-oc-16gb-graphics-card' => 'pny-graphics-card-geforce-rtx-5070-ti-oc.jpg',
        'pny-geforce-rtx-5080-oc-triple-fan-16gb-graphics-card' => 'pny-graphics-card-geforce-rtx-5080-oc-triple-fan.jpg',
        'zotac-gaming-geforce-rtx-5080-solid-core-oc-graphics-card' => 'zotac-graphics-card-gaming-geforce-rtx-5080-solid-core-oc.jpg',
        'zotac-geforce-rtx-5070-twin-edge-oc-graphics-card' => 'zotac-graphics-card-geforce-rtx-5070-twin-edge-oc.jpg',
        'gigabyte-aorus-prime-5-amd-ryzen-7-9700x-amd-b850-32gb-ram-1tb-ssd-rtx-5070-ti-gaming-pc' => 'gigabyte-brand-pc-aorus-prime-5-cover-thum.jpg',
        'pny-geforce-rtx-5080-oc-16gb-gddr7-triple-fan-graphics-card' => 'pny-graphics-card-geforce-rtx-5080-oc-triple-fan-cover-thum.jpg',
        'amd-ryzen-7-7700-msi-pro-b650m-p-16gb-ram-500gb-ssd-rtx-5070-gaming-pc-build' => 'amd-gaming-pc-amd-ryzen-7-7700-cover-thum.jpg',
        'gigabyte-geforce-rtx-5070-ti-windforce-oc-sff-16g-gddr7-graphics-card' => 'gigabyte-graphics-card-geforce-rtx-5070-ti-windforce-oc-sff-16g-cover-thum.jpg',
        'pny-geforce-rtx-5070-ti-oc-16gb-triple-fan-graphics-card' => 'pny-graphics-card-geforce-rtx-5070-ti-oc-cover-thum.jpg',
        'pny-geforce-rtx-5080-16gb-gddr7-argb-epic-x-rgb-oc-triple-fan-graphics-card' => 'pny-graphics-card-rtx-5080-16gb-gddr7-argb-epic-x-rgb-oc-triple-fan-cover-thum.jpg',
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
    ];

    /**
     * Featured category slug => icon under public/images/categories.
     *
     * @var list<string>
     */
    private const CATEGORY_IMAGES = [
        'smartphone', 'laptop', 'air-conditioner', 'desktop', 'ai-laptop', 'processor', 'motherboard', 'ssd',
        'graphics-card', 'ram', 'television', 'router', 'monitor', 'printer', 'air-fryer', 'projector',
    ];

    /**
     * [placement, image, alt]
     *
     * @var list<array{0: BannerPlacement, 1: string, 2: string}>
     */
    private const BANNERS = [
        [BannerPlacement::HeroMain, 'hero/slide-1.webp', 'Smartphone mega sale — save up to 25%'],
        [BannerPlacement::HeroMain, 'hero/slide-2.webp', 'Featured tech deals'],
        [BannerPlacement::HeroMain, 'hero/slide-3.webp', 'Featured tech deals'],
        [BannerPlacement::HeroMain, 'hero/slide-4.webp', 'Featured tech deals'],
        [BannerPlacement::HeroMain, 'hero/slide-5.webp', 'Smart living essentials'],
        [BannerPlacement::HeroSideTop, 'banners/logitech-mx-offer.webp', 'Upgrade your workspace with Logitech MX'],
        [BannerPlacement::HeroSideBottom, 'banners/msi-build-your-pc.webp', 'Build your PC with MSI and earn vouchers'],
        [BannerPlacement::Promo, 'banners/messenger-support.webp', 'Message us for product, stock, price and delivery info'],
    ];

    private const GAMING_SHOWCASE = [
        'amd-ryzen-7-7700-msi-pro-b650m-p-16gb-ram-500gb-ssd-rtx-5070-gaming-pc',
        'amd-ryzen-7-9700x-gaming-pc',
        'amd-ryzen-7-7700-processor',
        'unika-radeon-rx-580-blizzards-8gd5-v2-graphics-card',
        'asus-tuf-gaming-vg27aql5f-27-inch-qhd-210hz-monitor',
        'arzopa-z1fc-g1-game-max-161-inch-144hz-portable-gaming-monitor',
        'maxcool-inferno-mc5-micro-atx-gaming-computer-case',
        'redragon-h320-lamia-rgb-virtual-71-surround-sound-gaming-headset',
    ];

    private const NEW_SHOWCASE = [
        'jbl-partybox-720-800w-bluetooth-party-speaker',
        'kospet-tank-t3-ultra-2-143-inch-amoled-gps-bt-calling-smartwatch',
        'anker-solix-c1000-gen-2-portable-power-station',
        'asus-nuc-15-pro-core-5-210h-wi-fi-7-be202-mini-pc',
        'lg-34u640b-b-34-inch-wqhd-ultrawide-curved-monitor',
        'samsung-55u8510-55-inch-crystal-uhd-4k-smart-tv',
        'pny-geforce-rtx-5060-ti-8gb-epic-x-rgb-oc-graphics-card',
        'haier-hsu-19gravity-inv-pro-x6-15-ton-inverter-ac',
    ];

    public function run(SettingsService $settings, ?bool $withMedia = null): void
    {
        if (HomeSection::query()->where('type', HomeSectionType::Showcase)->exists()) {
            return;
        }

        $withMedia ??= app()->environment('local');

        $this->seedHomeLayout($withMedia);
        $this->seedSettings($settings, $withMedia);
        $this->publishPages();

        if ($withMedia) {
            $this->seedBanners();
            $this->attachCatalogImages();
        }
    }

    private function seedHomeLayout(bool $withMedia): void
    {
        $categoryId = fn (string $slug): ?int => Category::query()->where('slug', $slug)->value('id');
        $categoryUrl = fn (string $slug): string => route('store.categories.show', $slug);

        HomeSection::query()->delete();

        // [type, title, subtitle, settings, is_active, manual product slugs]
        $sections = [
            [HomeSectionType::Hero, null, null, [], true],
            [HomeSectionType::ServiceHighlights, null, null, [], false],
            [HomeSectionType::FeaturedCategories, 'Featured Categories', 'Explore our wide range of tech products', ['limit' => 16], true],
            [HomeSectionType::FlashSale, 'Flash Sale', 'Limited time deals', ['limit' => 12], true],
            [HomeSectionType::ProductCollection, 'Mobile Phones', 'Latest flagship and everyday smartphones from top brands', ['source' => ProductCollectionSource::Category->value, 'category_id' => $categoryId('smartphone'), 'limit' => 10], true],
            [HomeSectionType::ProductCollection, 'Smart Watches', 'Track your fitness and stay connected on the go', ['source' => ProductCollectionSource::Category->value, 'category_id' => $categoryId('smart-watch'), 'limit' => 10], true],
            [HomeSectionType::ProductCollection, 'Earbuds', 'True wireless sound for music, calls and gaming', ['source' => ProductCollectionSource::Category->value, 'category_id' => $categoryId('earbuds'), 'limit' => 10], true],
            [HomeSectionType::Showcase, 'Select AMD-powered gaming devices starting at ৳32,500', null, ['theme' => 'midnight', 'source' => ProductCollectionSource::Manual->value, 'cta_label' => 'Shop all', 'view_all_url' => $categoryUrl('gaming'), 'limit' => 8], true, self::GAMING_SHOWCASE],
            [HomeSectionType::ProductCollection, 'Featured Products', 'Discover our handpicked selection of premium tech products', ['source' => ProductCollectionSource::Featured->value, 'limit' => 12], true],
            [HomeSectionType::Showcase, 'New and Noteworthy', 'Explore the latest finds from innovative tech and trending product releases.', ['theme' => 'aurora', 'source' => ProductCollectionSource::Manual->value, 'cta_label' => 'Shop now', 'view_all_url' => $categoryUrl('gadget'), 'limit' => 8], true, self::NEW_SHOWCASE],
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
                foreach (['amd-gaming-pc-amd-ryzen-7-9700x.jpg', 'asus-monitor-tuf-series-5-vg27aql5f.jpg'] as $image) {
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

        $settings->set('general.logo', $this->copyToPublicDisk('brand/tcb-logo.png', 'settings'));
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
        foreach (self::BANNERS as $order => [$placement, $image, $alt]) {
            $banner = Banner::query()->create([
                'placement' => $placement,
                'title' => $alt,
                'alt_text' => $alt,
                'url' => '#',
                'sort_order' => $order + 1,
            ]);

            $banner->addMedia(public_path("images/{$image}"))->preservingOriginal()->toMediaCollection('desktop');
        }
    }

    private function attachCatalogImages(): void
    {
        Product::query()
            ->whereIn('slug', array_keys(self::PRODUCT_IMAGES))
            ->whereDoesntHave('media', fn ($media) => $media->where('collection_name', 'main'))
            ->each(fn (Product $product) => $product
                ->addMedia(public_path('images/products/'.self::PRODUCT_IMAGES[$product->slug]))
                ->preservingOriginal()
                ->toMediaCollection('main'));

        Category::query()
            ->whereIn('slug', self::CATEGORY_IMAGES)
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
