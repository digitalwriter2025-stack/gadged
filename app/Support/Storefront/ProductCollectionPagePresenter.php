<?php

namespace App\Support\Storefront;

use App\Models\Product;
use App\Services\ProductFilterService;
use App\Support\Localization;
use Illuminate\Database\Eloquent\Builder;

/**
 * Cross-category product listings (trending products, combo deals) rendered with
 * the category listing view (store/category.blade.php) and its filters.
 *
 * @phpstan-import-type Filters from ProductFilterService
 */
class ProductCollectionPagePresenter
{
    public function __construct(
        private readonly ProductFilterService $filterService,
        private readonly ProductCardPresenter $cards,
        private readonly CategoryPagePresenter $categoryPresenter,
    ) {}

    /**
     * @param  Filters  $filters
     * @return array<string, mixed>
     */
    public function trending(array $filters): array
    {
        $store = config('app.name');

        return $this->present(
            base: Product::query()->published()->trending(),
            filters: $filters,
            url: route('store.trending'),
            heading: __('Trending Gadgets'),
            intro: __('The smartwatches, earbuds, speakers, power banks and phones our customers are buying and asking about most right now at :store, with delivery across Bangladesh.', ['store' => $store]),
            seoTitle: __('Trending Gadgets in Bangladesh :year | :store', ['year' => Localization::digits(now()->year), 'store' => $store]),
            emptyState: [
                'title' => __('No trending products right now'),
                'text' => __('Our team updates this list as new gadgets catch on. Meanwhile, browse the latest arrivals on the homepage.'),
            ],
        );
    }

    /**
     * @param  Filters  $filters
     * @return array<string, mixed>
     */
    public function combos(array $filters): array
    {
        $store = config('app.name');

        return $this->present(
            base: Product::query()->published()->combos(),
            filters: $filters,
            url: route('store.combos.index'),
            heading: __('Combo Deals'),
            intro: __('Gadget bundles priced together for less than buying each item on its own. Every combo lists exactly what is in the box, and the whole set is delivered in one order from :store.', ['store' => $store]),
            seoTitle: __('Gadget Combo Offers in Bangladesh :year | :store', ['year' => Localization::digits(now()->year), 'store' => $store]),
            emptyState: [
                'title' => __('No combo deals right now'),
                'text' => __('New bundles are added regularly. Check our running campaigns for current discounts.'),
                'action' => ['label' => __('View Campaigns'), 'url' => route('store.offers.index')],
            ],
        );
    }

    /**
     * @param  Builder<Product>  $base
     * @param  Filters  $filters
     * @param  array{title: string, text: string, action?: array{label: string, url: string}}  $emptyState
     * @return array<string, mixed>
     */
    private function present(Builder $base, array $filters, string $url, string $heading, string $intro, string $seoTitle, array $emptyState): array
    {
        $facets = $this->filterService->collectionFacets(clone $base);

        $products = $this->filterService
            ->apply((clone $base)->forCard()->with(ProductCardPresenter::relations()), [...$filters, 'specs' => []], $facets)
            ->paginate(ProductFilterService::PER_PAGE)
            ->withQueryString()
            ->through(fn (Product $product): array => $this->cards->present($product));

        $activeFilterCount = count($filters['stock']) + count($filters['brands'])
            + ($filters['min'] !== null || $filters['max'] !== null ? 1 : 0)
            + ($filters['q'] !== null ? 1 : 0);

        return [
            'category' => ['name' => $heading, 'url' => $url, 'heading' => $heading, 'intro' => $intro],
            'breadcrumbs' => [
                ['label' => __('Home'), 'url' => route('store.home')],
                ['label' => $heading, 'url' => null],
            ],
            'subcategories' => [],
            'filters' => $filters,
            'filterGroups' => $this->categoryPresenter->filterGroups($facets, $filters),
            'price' => $facets['price'],
            'activeFilterCount' => $activeFilterCount,
            'sorts' => ProductFilterService::sortOptions(),
            'products' => $products,
            'emptyState' => $emptyState,
            'seo' => [
                'title' => $seoTitle,
                'description' => $intro,
                'canonical' => $products->currentPage() > 1 ? $url.'?page='.$products->currentPage() : $url,
                'noindex' => $activeFilterCount > 0 || $filters['sort'] !== ProductFilterService::DEFAULT_SORT,
            ],
        ];
    }
}
