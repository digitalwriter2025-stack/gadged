<?php

namespace App\Support\Storefront;

use App\Models\Product;
use App\Services\ProductFilterService;

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

        $products = $this->filterService
            ->apply($this->filterService->searchQuery($term)->forCard()->with(ProductCardPresenter::relations()), [...$filters, 'q' => null], $facets)
            ->paginate(ProductFilterService::PER_PAGE)
            ->withQueryString()
            ->through(fn (Product $product): array => $this->cards->present($product));

        $activeFilterCount = count($filters['stock']) + count($filters['brands'])
            + ($filters['min'] !== null || $filters['max'] !== null ? 1 : 0);

        $url = route('store.search', ['q' => $term]);
        $heading = "Search results for “{$term}”";
        $intro = $facets['total'] === 0
            ? "We couldn't find any products matching “{$term}”. Check the spelling or try a more general term."
            : number_format($facets['total'])." products found for “{$term}” at ".config('app.name').'.';

        return [
            'category' => ['name' => 'search results', 'url' => $url, 'heading' => $heading, 'intro' => $intro],
            'breadcrumbs' => [
                ['label' => 'Home', 'url' => route('store.home')],
                ['label' => 'Search', 'url' => null],
            ],
            'subcategories' => [],
            'filters' => [...$filters, 'q' => $term],
            'filterGroups' => $this->categoryPresenter->filterGroups($facets, $filters),
            'price' => $facets['price'],
            'activeFilterCount' => $activeFilterCount,
            'sorts' => ProductFilterService::SORTS,
            'products' => $products,
            'seo' => [
                'title' => "{$term} - Search Results",
                'description' => $intro,
                'canonical' => $url,
                'noindex' => true,
            ],
        ];
    }
}
