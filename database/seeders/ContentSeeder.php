<?php

namespace Database\Seeders;

use App\Enums\HomeSectionType;
use App\Enums\ProductCollectionSource;
use App\Models\HomeSection;
use App\Models\Menu;
use App\Models\Page;
use App\Models\ServiceHighlight;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * CMS structure matching the reference layout: homepage section order, the
 * service strip, footer menus and their policy pages.
 *
 * Pages are created inactive with empty content — staff write the real text
 * in the admin panel before publishing. Banners are not seeded (they need artwork).
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedHomeSections();
        $this->seedServiceHighlights();
        $this->seedMenus();
    }

    private function seedHomeSections(): void
    {
        if (HomeSection::query()->exists()) {
            return;
        }

        // [type, title, subtitle, settings, is_active]
        $sections = [
            [HomeSectionType::Hero, null, null, [], true],
            [HomeSectionType::ServiceHighlights, null, null, [], false],
            [HomeSectionType::FeaturedCategories, 'Featured Categories', 'Explore our wide range of tech products', ['limit' => 16], true],
            [HomeSectionType::FlashSale, 'Flash Sale', 'Limited time deals', ['limit' => 12], true],
            [HomeSectionType::ProductCollection, 'Featured Products', 'Discover our handpicked selection of premium tech products', ['source' => ProductCollectionSource::Featured->value, 'limit' => 12], true],
            [HomeSectionType::ProductCollection, 'Latest Products', 'Check out our newest arrivals', ['source' => ProductCollectionSource::Latest->value, 'limit' => 12], true],
            [HomeSectionType::PromoBanners, null, null, [], true],
            [HomeSectionType::ProductCollection, 'Best Sellers', 'Top-rated products loved by our customers', ['source' => ProductCollectionSource::BestSellers->value, 'limit' => 12], true],
            [HomeSectionType::ProductGrid, 'All Product', 'Browse our complete collection', ['source' => ProductCollectionSource::Latest->value, 'limit' => 40, 'initial_count' => 5], true],
        ];

        foreach ($sections as $order => [$type, $title, $subtitle, $settings, $isActive]) {
            HomeSection::query()->create([
                'type' => $type,
                'title' => $title,
                'subtitle' => $subtitle,
                'settings' => $settings,
                'is_active' => $isActive,
                'sort_order' => $order + 1,
            ]);
        }
    }

    private function seedServiceHighlights(): void
    {
        if (ServiceHighlight::query()->exists()) {
            return;
        }

        $items = [
            ['cash', '0% EMI'],
            ['support', '24/7 Online Support'],
            ['card', 'No charge on card payment'],
            ['truck', 'Cash on delivery in 64 districts'],
        ];

        foreach ($items as $order => [$icon, $title]) {
            ServiceHighlight::query()->create(['icon' => $icon, 'title' => $title, 'sort_order' => $order + 1]);
        }
    }

    private function seedMenus(): void
    {
        $menus = [
            'footer_information' => ['Information', [
                ['About Us', 'about-us'],
                ['Brands', '/brands'],
                ['Affiliation', 'affiliation'],
                ['Service Centers', 'service-centers'],
                ['EMI Information', 'emi-information'],
            ]],
            'footer_policies' => ['Policies', [
                ['Privacy Policy', 'privacy-policy'],
                ['Warranty Policy', 'warranty-policy'],
                ['Payment Terms', 'payment-terms'],
                ['Delivery Policy', 'delivery-policy'],
                ['Terms & Conditions', 'terms-and-conditions'],
                ['Refund and Return Policy', 'refund-and-return-policy'],
            ]],
        ];

        foreach ($menus as $location => [$name, $links]) {
            $menu = Menu::query()->firstOrCreate(['location' => $location], ['name' => $name]);

            if ($menu->items()->exists()) {
                continue;
            }

            foreach ($links as $order => [$label, $target]) {
                $item = ['label' => $label, 'sort_order' => $order + 1];

                if (str_starts_with($target, '/')) {
                    $item['url'] = $target;
                } else {
                    $page = Page::query()->firstOrCreate(
                        ['slug' => Str::slug($target)],
                        ['title' => $label, 'content' => null, 'is_active' => false],
                    );

                    $item['linkable_type'] = $page->getMorphClass();
                    $item['linkable_id'] = $page->id;
                }

                $menu->items()->create($item);
            }
        }
    }
}
