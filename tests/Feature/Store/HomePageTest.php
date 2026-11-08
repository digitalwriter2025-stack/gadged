<?php

namespace Tests\Feature\Store;

use App\Enums\BannerPlacement;
use App\Enums\FlashSaleDiscountType;
use App\Enums\HomeSectionType;
use App\Enums\ProductCollectionSource;
use App\Models\Banner;
use App\Models\Category;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\HomeSection;
use App\Models\Menu;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    private int $sortOrder = 0;

    public function test_sections_render_in_admin_order_and_inactive_sections_are_hidden(): void
    {
        Product::factory()->create(['name' => 'Ryzen Desktop Bundle']);

        $this->section(HomeSectionType::ProductCollection, ['title' => 'Best Sellers', 'settings' => ['source' => ProductCollectionSource::BestSellers->value]]);
        $this->section(HomeSectionType::ProductCollection, ['title' => 'Latest Products', 'settings' => ['source' => ProductCollectionSource::Latest->value]]);
        $this->section(HomeSectionType::ProductCollection, ['title' => 'Hidden Picks', 'is_active' => false]);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSeeInOrder(['Best Sellers', 'Ryzen Desktop Bundle', 'Latest Products', 'Ryzen Desktop Bundle'])
            ->assertDontSee('Hidden Picks');
    }

    public function test_product_sections_without_products_are_not_rendered(): void
    {
        $emptyCategory = Category::factory()->create();

        $this->section(HomeSectionType::ProductCollection, [
            'title' => 'Empty Category Picks',
            'settings' => ['source' => ProductCollectionSource::Category->value, 'category_id' => $emptyCategory->id],
        ]);
        $this->section(HomeSectionType::FlashSale, ['title' => 'Flash Sale']);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertDontSee('Empty Category Picks')
            ->assertDontSee('data-countdown', false);
    }

    public function test_hero_and_promo_banners_render_uploaded_artwork_within_their_schedule(): void
    {
        Storage::fake('public');

        $this->banner(BannerPlacement::HeroMain, 'Smartphone mega sale');
        $this->banner(BannerPlacement::HeroMain, 'Laptop week');
        $this->banner(BannerPlacement::HeroMain, 'Starts next week', ['starts_at' => now()->addWeek()]);
        $this->banner(BannerPlacement::HeroSideTop, 'Workspace upgrade');
        $this->banner(BannerPlacement::Promo, 'Message us for stock info');
        Banner::factory()->create(['placement' => BannerPlacement::HeroMain, 'alt_text' => 'Banner without artwork']);

        $this->section(HomeSectionType::Hero);
        $this->section(HomeSectionType::PromoBanners);

        $html = $this->get(route('store.home'))
            ->assertOk()
            ->assertSeeInOrder(['Smartphone mega sale', 'Laptop week', 'Workspace upgrade', 'Message us for stock info'])
            ->assertSee('data-slide-next', false)
            ->assertDontSee('Starts next week')
            ->assertDontSee('Banner without artwork')
            ->getContent();

        $this->assertSame(2, substr_count($html, 'data-slide '));
    }

    public function test_flash_sale_cards_show_deal_price_savings_and_server_countdown(): void
    {
        $sale = FlashSale::factory()->create(['ends_at' => now()->addDays(2)->startOfMinute()]);
        $headset = Product::factory()->priced('3600')->create(['name' => 'Redragon H320 Lamia Headset']);
        FlashSaleProduct::factory()->for($sale)->for($headset)->create([
            'discount_type' => FlashSaleDiscountType::FixedPrice,
            'discount_value' => '2949',
        ]);
        FlashSaleProduct::factory()->for($sale)->create(['quantity_limit' => 5, 'sold_quantity' => 5]);
        FlashSaleProduct::factory()->for(FlashSale::factory()->ended())->for(Product::factory()->state(['name' => 'Expired Deal Mouse']))->create();

        $this->section(HomeSectionType::FlashSale, ['title' => 'Flash Sale']);

        $html = $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('Redragon H320 Lamia Headset')
            ->assertSee('Save: ৳651')
            ->assertSee('৳2,949')
            ->assertSee('data-ends-at="'.$sale->ends_at->toIso8601String().'"', false)
            ->assertDontSee('Expired Deal Mouse')
            ->getContent();

        $this->assertSame(1, substr_count($html, 'data-countdown '));
    }

    public function test_category_collection_lists_subtree_products_and_links_to_the_category(): void
    {
        $phones = Category::factory()->create(['name' => 'Phones', 'slug' => 'phones']);
        $smartphones = Category::factory()->childOf($phones)->create();
        Product::factory()->create(['name' => 'Pixel 9 Pro', 'category_id' => $smartphones->id]);
        Product::factory()->create(['name' => 'Office Chair']);

        $this->section(HomeSectionType::ProductCollection, [
            'title' => 'Mobile Phones',
            'settings' => ['source' => ProductCollectionSource::Category->value, 'category_id' => $phones->id],
        ]);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSeeInOrder(['Mobile Phones', 'href="'.route('store.categories.show', 'phones').'"', 'Pixel 9 Pro'], false)
            ->assertDontSee('Office Chair');
    }

    public function test_manual_showcase_keeps_hand_picked_order_and_skips_unpublished_products(): void
    {
        $speaker = Product::factory()->priced('118800', '102500')->create(['name' => 'JBL Partybox 720']);
        $watch = Product::factory()->create(['name' => 'Kospet Tank T3']);
        $draft = Product::factory()->draft()->create(['name' => 'Draft Gadget']);

        $section = $this->section(HomeSectionType::Showcase, [
            'title' => 'New and Noteworthy',
            'subtitle' => 'Explore the latest finds',
            'settings' => ['source' => ProductCollectionSource::Manual->value, 'theme' => 'aurora', 'cta_label' => 'Shop now'],
        ]);

        foreach ([$watch, $draft, $speaker] as $position => $product) {
            $section->items()->create(['itemable_type' => $product->getMorphClass(), 'itemable_id' => $product->id, 'sort_order' => $position]);
        }

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('data-carousel="showcase"', false)
            ->assertSee('Explore the latest finds')
            ->assertSeeInOrder(['New and Noteworthy', 'Kospet Tank T3', 'JBL Partybox 720'])
            ->assertSee('Save ৳16,300')
            ->assertDontSee('Draft Gadget');
    }

    public function test_product_grid_shows_the_initial_count_and_hides_the_rest(): void
    {
        Product::factory()->count(7)->create();

        $this->section(HomeSectionType::ProductGrid, ['title' => 'All Product', 'settings' => ['initial_count' => 5]]);

        $html = $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('data-product-grid-more', false)
            ->assertSee('Showing <span data-product-grid-shown>5</span> of 7 products', false)
            ->getContent();

        preg_match_all('/<li data-product-grid-item(\s+hidden)?\s*>/', $html, $matches);

        $this->assertCount(7, $matches[0]);
        $this->assertCount(2, array_filter($matches[1]));
    }

    public function test_featured_categories_show_only_active_featured_categories(): void
    {
        Category::factory()->featured()->create(['name' => 'Graphics Card']);
        Category::factory()->featured()->inactive()->create(['name' => 'Retired Category']);
        Category::factory()->create(['name' => 'Plain Category', 'show_in_menu' => false]);

        $this->section(HomeSectionType::FeaturedCategories, ['title' => 'Featured Categories']);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSeeInOrder(['Featured Categories', 'Graphics Card'])
            ->assertDontSee('Retired Category')
            ->assertDontSee('Plain Category');
    }

    public function test_homepage_cards_link_to_product_pages(): void
    {
        $product = Product::factory()->create();

        $this->section(HomeSectionType::ProductCollection, ['title' => 'Latest Products']);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee(route('store.products.show', $product->slug), false);
    }

    public function test_mobile_navigation_and_reveal_hooks_are_rendered(): void
    {
        Category::factory()->create(['name' => 'Desktop']);
        Menu::query()->create(['name' => 'Information', 'location' => 'footer_information'])->items()->create(['label' => 'Brands', 'url' => '/brands']);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('<dialog id="mobile-menu"', false)
            ->assertSee('data-drawer-open="mobile-menu"', false)
            ->assertSee('aria-label="Quick navigation"', false)
            ->assertSee('Desktop')
            ->assertSee('data-footer-accordion', false)
            ->assertSee("document.documentElement.classList.add('js')", false);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function section(HomeSectionType $type, array $attributes = []): HomeSection
    {
        return HomeSection::query()->create([
            'type' => $type,
            'settings' => [],
            'is_active' => true,
            'sort_order' => ++$this->sortOrder,
            ...$attributes,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function banner(BannerPlacement $placement, string $alt, array $attributes = []): Banner
    {
        $banner = Banner::factory()->create(['placement' => $placement, 'alt_text' => $alt, 'sort_order' => ++$this->sortOrder, ...$attributes]);
        $banner->addMedia(UploadedFile::fake()->image('banner.jpg', 1672, 941))->toMediaCollection('desktop');

        return $banner;
    }
}
