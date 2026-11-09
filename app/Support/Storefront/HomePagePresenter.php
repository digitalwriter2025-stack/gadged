<?php

namespace App\Support\Storefront;

use App\Enums\BannerPlacement;
use App\Enums\HomeSectionType;
use App\Enums\ProductCollectionSource;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\HomeSection;
use App\Models\HomeSectionItem;
use App\Models\Product;
use App\Models\ServiceHighlight;
use App\Services\CatalogCache;
use App\Support\Localization;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Builds the homepage from the ordered, admin-managed home_sections rows.
 *
 * Section layout, banners and the product IDs of each section are cached in
 * CatalogCache (flushed on every admin change, and expiring so "latest" and
 * "best seller" lists follow new sales). Product rows, prices and stock are
 * loaded fresh on every request in a single query, and flash sales are always
 * resolved against server time.
 *
 * @phpstan-type Blueprint array<string, mixed>
 */
class HomePagePresenter
{
    public const CACHE_SECONDS = 600;

    public const DEFAULT_PRODUCT_LIMIT = 12;

    public const MAX_PRODUCT_LIMIT = 60;

    /**
     * Section types whose product list is rendered as cards; they are hidden when empty.
     */
    private const PRODUCT_SECTIONS = [
        HomeSectionType::FlashSale,
        HomeSectionType::ProductCollection,
        HomeSectionType::Showcase,
        HomeSectionType::ProductGrid,
    ];

    public function __construct(
        private readonly CatalogCache $cache,
        private readonly ProductCardPresenter $cards,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function sections(): array
    {
        /** @var list<Blueprint> $blueprints */
        $blueprints = $this->cache->remember('home.sections', self::CACHE_SECONDS, fn (): array => $this->blueprints());

        $flashSale = $this->runningFlashSale();

        $productIds = collect($blueprints)->pluck('product_ids')->flatten()
            ->merge($flashSale?->items->pluck('product_id') ?? [])
            ->unique()
            ->values()
            ->all();

        $products = $productIds === []
            ? new Collection
            : Product::query()->published()->forCard()->whereIn('products.id', $productIds)->get()->keyBy('id');

        // The same product usually appears in several sections, so present each card once.
        $cards = $products->map(fn (Product $product): array => $this->cards->present($product))->all();

        return collect($blueprints)
            ->map(fn (array $blueprint): array => $this->hydrate($blueprint, $products, $cards, $flashSale))
            ->reject(fn (array $section): bool => $this->isEmpty($section))
            ->values()
            ->all();
    }

    /**
     * Cacheable description of every active section: static content plus product IDs.
     *
     * @return list<Blueprint>
     */
    private function blueprints(): array
    {
        return HomeSection::query()
            ->active()
            ->with(['items', 'media'])
            ->get()
            ->map(fn (HomeSection $section): array => [
                'type' => $section->type->value,
                'title' => $section->translated('title'),
                'subtitle' => $section->translated('subtitle'),
                'product_ids' => [],
                ...$this->content($section),
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function content(HomeSection $section): array
    {
        return match ($section->type) {
            HomeSectionType::Hero => [
                'slides' => $this->banners(BannerPlacement::HeroMain),
                'side' => [
                    ...array_slice($this->banners(BannerPlacement::HeroSideTop), 0, 1),
                    ...array_slice($this->banners(BannerPlacement::HeroSideBottom), 0, 1),
                ],
            ],
            HomeSectionType::PromoBanners => ['banners' => $this->banners(BannerPlacement::Promo)],
            HomeSectionType::ServiceHighlights => ['items' => ServiceHighlight::query()->active()->get(['icon', 'title', 'title_bn', 'subtitle', 'subtitle_bn', 'url'])
                ->map(fn (ServiceHighlight $highlight): array => [
                    'icon' => $highlight->icon,
                    'title' => $highlight->translated('title'),
                    'subtitle' => $highlight->translated('subtitle'),
                    'url' => $highlight->url,
                ])
                ->all()],
            HomeSectionType::FeaturedCategories => ['categories' => $this->featuredCategories($this->limit($section, 16))],
            HomeSectionType::FeaturedBrands => ['brands' => $this->featuredBrands($this->limit($section, 12))],
            HomeSectionType::FlashSale => [
                'limit' => $this->limit($section),
                'view_all_url' => $section->setting('view_all_url'),
            ],
            HomeSectionType::ProductCollection => [
                'product_ids' => $this->productIds($section, $category = $this->sourceCategory($section)),
                'view_all_url' => $this->viewAllUrl($section, $category),
            ],
            HomeSectionType::ProductGrid => [
                'product_ids' => $this->productIds($section, $this->sourceCategory($section), 40),
                'initial_count' => max(1, (int) $section->setting('initial_count', 5)),
            ],
            HomeSectionType::Showcase => [
                'product_ids' => $this->productIds($section, $category = $this->sourceCategory($section)),
                'theme' => $section->setting('theme', 'aurora'),
                // An untranslated label such as the default "Shop now" still goes through the language file.
                'cta_label' => $section->setting(Localization::column('cta_label')) ?: __($section->setting('cta_label') ?: 'Shop now'),
                'cta_url' => $this->viewAllUrl($section, $category) ?? '#',
                'images' => $section->getMedia('images')
                    ->map(fn (Media $media): string => $media->getAvailableUrl(['card']))
                    ->all(),
            ],
        };
    }

    /**
     * @return list<int>
     */
    private function productIds(HomeSection $section, ?Category $category, int $defaultLimit = self::DEFAULT_PRODUCT_LIMIT): array
    {
        $limit = $this->limit($section, $defaultLimit);
        $source = ProductCollectionSource::tryFrom((string) $section->setting('source')) ?? ProductCollectionSource::Latest;

        if ($source === ProductCollectionSource::Manual) {
            return $section->items
                ->where('itemable_type', (new Product)->getMorphClass())
                ->take($limit)
                ->map(fn (HomeSectionItem $item): int => $item->itemable_id)
                ->values()
                ->all();
        }

        $query = Product::query()->published()->limit($limit);

        match ($source) {
            ProductCollectionSource::Latest => $query->orderByDesc('is_latest')->latest('published_at')->latest('id'),
            ProductCollectionSource::BestSellers => $query->orderByDesc('is_best_seller')->orderByDesc('sales_count')->latest('id'),
            ProductCollectionSource::Featured => $query->featured()->latest('published_at')->latest('id'),
            ProductCollectionSource::Trending => $query->trending()->latest('published_at')->latest('id'),
            ProductCollectionSource::Combos => $query->combos()->latest('published_at')->latest('id'),
            ProductCollectionSource::Category => $category === null
                ? $query->whereRaw('1 = 0')
                : $query->inCategories($category->subtreeIds())->orderByDesc('is_featured')->latest('id'),
        };

        return $query->pluck('id')->all();
    }

    private function sourceCategory(HomeSection $section): ?Category
    {
        $categoryId = $section->setting('category_id');

        return $categoryId === null ? null : Category::query()->active()->find($categoryId);
    }

    /**
     * An explicit "view all" link wins; a category collection links to its category,
     * and trending / combo collections link to their listing pages.
     */
    private function viewAllUrl(HomeSection $section, ?Category $category): ?string
    {
        if (filled($section->setting('view_all_url'))) {
            return $section->setting('view_all_url');
        }

        if ($category !== null && $section->setting('source') === ProductCollectionSource::Category->value) {
            return route('store.categories.show', $category->slug);
        }

        return match (ProductCollectionSource::tryFrom((string) $section->setting('source'))) {
            ProductCollectionSource::Trending => route('store.trending'),
            ProductCollectionSource::Combos => route('store.combos.index'),
            default => null,
        };
    }

    private function limit(HomeSection $section, int $default = self::DEFAULT_PRODUCT_LIMIT): int
    {
        return min(self::MAX_PRODUCT_LIMIT, max(1, (int) $section->setting('limit', $default)));
    }

    /**
     * @return list<array{image: string, mobile_image: ?string, alt: string, url: string, new_tab: bool}>
     */
    private function banners(BannerPlacement $placement): array
    {
        return Banner::query()
            ->placement($placement)
            ->visible()
            ->with('media')
            ->get()
            ->map(fn (Banner $banner): ?array => ($image = $banner->imageFor('desktop')) === null ? null : [
                'image' => $image->getAvailableUrl(['desktop']),
                'mobile_image' => $banner->imageFor('mobile')?->getAvailableUrl(['mobile']),
                'alt' => $banner->translated('alt_text') ?: (string) $banner->translated('title'),
                'url' => $banner->url ?: '#',
                'new_tab' => $banner->open_in_new_tab,
            ])
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return list<array{name: string, url: string, image: ?string}>
     */
    private function featuredCategories(int $limit): array
    {
        return Category::query()
            ->active()
            ->featured()
            ->ordered()
            ->with('media')
            ->limit($limit)
            ->get(['id', 'name', 'name_bn', 'slug'])
            ->map(fn (Category $category): array => [
                'name' => $category->translated('name'),
                'url' => route('store.categories.show', $category->slug),
                'image' => $category->getFirstMedia('image')?->getAvailableUrl(['thumb']),
            ])
            ->all();
    }

    /**
     * @return list<array{name: string, url: string, image: ?string}>
     */
    private function featuredBrands(int $limit): array
    {
        return Brand::query()
            ->active()
            ->featured()
            ->with('media')
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'website'])
            ->map(fn (Brand $brand): array => [
                'name' => $brand->name,
                'url' => route('store.search', ['q' => $brand->name]),
                'image' => $brand->getFirstMedia('logo')?->getAvailableUrl(['card']),
            ])
            ->all();
    }

    private function runningFlashSale(): ?FlashSale
    {
        return FlashSale::query()
            ->running()
            ->with(['items' => fn ($items) => $items->whereNull('product_variant_id')])
            ->orderBy('sort_order')
            ->orderBy('ends_at')
            ->first();
    }

    /**
     * @param  Blueprint  $blueprint
     * @param  Collection<int, Product>  $products
     * @param  array<int, array<string, mixed>>  $cards  Presented cards keyed by product ID.
     * @return array<string, mixed>
     */
    private function hydrate(array $blueprint, Collection $products, array $cards, ?FlashSale $flashSale): array
    {
        if ($blueprint['type'] === HomeSectionType::FlashSale->value) {
            $blueprint['products'] = $flashSale === null ? [] : $flashSale->items
                ->reject(fn (FlashSaleProduct $item): bool => $item->isSoldOut() || ! $products->has($item->product_id))
                ->take($blueprint['limit'])
                ->map(fn (FlashSaleProduct $item): array => $this->cards->presentFlashDeal($products->get($item->product_id), $item, $flashSale))
                ->values()
                ->all();
            $blueprint['title'] ??= $flashSale?->translated('title');
        } elseif (in_array(HomeSectionType::from($blueprint['type']), self::PRODUCT_SECTIONS, true)) {
            $blueprint['products'] = collect($blueprint['product_ids'])
                ->filter(fn (int $id): bool => isset($cards[$id]))
                ->map(fn (int $id): array => $cards[$id])
                ->values()
                ->all();
        }

        return Arr::except($blueprint, ['product_ids', 'limit']);
    }

    /**
     * @param  array<string, mixed>  $section
     */
    private function isEmpty(array $section): bool
    {
        return match (HomeSectionType::from($section['type'])) {
            HomeSectionType::Hero => $section['slides'] === [] && $section['side'] === [],
            HomeSectionType::PromoBanners => $section['banners'] === [],
            HomeSectionType::ServiceHighlights => $section['items'] === [],
            HomeSectionType::FeaturedCategories => $section['categories'] === [],
            HomeSectionType::FeaturedBrands => $section['brands'] === [],
            default => ($section['products'] ?? []) === [],
        };
    }
}
