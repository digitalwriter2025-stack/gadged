<?php

namespace App\Support\Storefront;

use App\Models\Product;
use App\Services\ProductFilterService;
use App\Support\Localization;

/**
 * Turns a search term and the shopper's filters into the data rendered by the
 * product listing view (store/category.blade.php), searching the whole catalog.
 *
 * @phpstan-import-type Filters from ProductFilterService
 */
class SearchPagePresenter
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
    public function present(string $term, array $filters): array
    {
        $facets = $this->filterService->searchFacets($term);

        $query = $this->filterService->searchQuery($term)->forCard()->with(ProductCardPresenter::relations());

        if ($filters['sort'] === ProductFilterService::DEFAULT_SORT) {
            $this->filterService->orderByRelevance($query, $term);
        }

        $products = $this->filterService
            ->apply($query, [...$filters, 'q' => null], $facets)
            ->paginate(ProductFilterService::PER_PAGE)
            ->withQueryString()
            ->through(fn (Product $product): array => $this->cards->present($product));

        $activeFilterCount = count($filters['stock']) + count($filters['brands'])
            + ($filters['min'] !== null || $filters['max'] !== null ? 1 : 0);

        $url = route('store.search', ['q' => $term]);
        $heading = __('Search results for “:term”', ['term' => $term]);
        $intro = $facets['total'] === 0
            ? __('We couldn\'t find any products matching “:term”. Check the spelling or try a more general term.', ['term' => $term])
            : __(':count products found for “:term” at :store.', ['count' => Localization::digits(number_format($facets['total'])), 'term' => $term, 'store' => config('app.name')]);

        return [
            'category' => ['name' => __('search results'), 'url' => $url, 'heading' => $heading, 'intro' => $intro],
            'breadcrumbs' => [
                ['label' => __('Home'), 'url' => route('store.home')],
                ['label' => __('Search'), 'url' => null],
            ],
            'subcategories' => [],
            'filters' => [...$filters, 'q' => $term],
            'filterGroups' => $this->categoryPresenter->filterGroups($facets, $filters),
            'price' => $facets['price'],
            'activeFilterCount' => $activeFilterCount,
            'sorts' => ProductFilterService::sortOptions(),
            'products' => $products,
            'seo' => [
                'title' => __(':term - Search Results', ['term' => $term]),
                'description' => $intro,
                'canonical' => $url,
                'noindex' => true,
            ],
        ];
    }
}
