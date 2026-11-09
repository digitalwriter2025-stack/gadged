<?php

namespace App\Support\Storefront;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductFilterService;
use App\Support\Localization;
use App\Support\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

/**
 * Turns a category and the shopper's filters into the data rendered by store/category.blade.php.
 *
 * @phpstan-import-type Filters from ProductFilterService
 * @phpstan-import-type FacetOption from ProductFilterService
 *
 * @phpstan-type FilterOption array{value: string, label: string, count: int, checked: bool}
 * @phpstan-type FilterGroup array{key: string, name: string, input: string, open: bool, options: list<FilterOption>}
 */
class CategoryPagePresenter
{
    public function __construct(
        private readonly ProductFilterService $filterService,
        private readonly ProductCardPresenter $cards,
    ) {}

    /**
     * @param  Filters  $filters
     * @return array{
     *     category: array{name: string, url: string, heading: string, intro: string},
     *     breadcrumbs: list<array{label: string, url: ?string}>,
     *     subcategories: list<array{name: string, url: string}>,
     *     filters: Filters,
     *     filterGroups: list<FilterGroup>,
     *     price: array{min: int, max: int},
     *     activeFilterCount: int,
     *     sorts: array<string, string>,
     *     products: LengthAwarePaginator<int, array<string, mixed>>,
     *     emptyState: array{title: string, text: string, action: array{label: string, url: string}},
     *     seo: array{title: string, description: string, canonical: string, noindex: bool}
     * }
     */
    public function present(Category $category, array $filters): array
    {
        $categoryIds = $category->subtreeIds();
        $facets = $this->filterService->facets($categoryIds);

        $products = $this->filterService
            ->apply($this->filterService->baseQuery($categoryIds)->forCard()->with(ProductCardPresenter::relations()), $filters, $facets)
            ->paginate(ProductFilterService::PER_PAGE)
            ->withQueryString()
            ->through(fn (Product $product): array => $this->cards->present($product));

        $activeFilterCount = count($filters['stock']) + count($filters['brands'])
            + count(array_merge([], ...array_values($filters['specs'])))
            + ($filters['min'] !== null || $filters['max'] !== null ? 1 : 0)
            + ($filters['q'] !== null ? 1 : 0);

        $url = route('store.categories.show', $category->slug);
        $name = $category->translated('name');
        $heading = __(':name Price in Bangladesh', ['name' => $name]);
        $intro = $this->intro($category, $facets['price'], $facets['total']);

        return [
            'category' => ['name' => $name, 'url' => $url, 'heading' => $heading, 'intro' => $intro],
            'breadcrumbs' => $this->breadcrumbs($category),
            'subcategories' => $this->subcategories($category),
            'filters' => $filters,
            'filterGroups' => $this->filterGroups($facets, $filters),
            'price' => $facets['price'],
            'activeFilterCount' => $activeFilterCount,
            'sorts' => ProductFilterService::sortOptions(),
            'products' => $products,
            'emptyState' => [
                'title' => __('No :name products listed yet', ['name' => $name]),
                'text' => __('Call or message us to ask about availability, or see which gadgets are trending right now.'),
                'action' => ['label' => __('See Trending Gadgets'), 'url' => route('store.trending')],
            ],
            'seo' => [
                'title' => $category->translated('seo_title') ?: $heading.' '.Localization::digits(now()->year),
                'description' => $category->translated('seo_description') ?: Str::limit($intro, 160),
                'canonical' => $products->currentPage() > 1 ? $url.'?page='.$products->currentPage() : $url,
                'noindex' => $activeFilterCount > 0 || $filters['sort'] !== ProductFilterService::DEFAULT_SORT,
            ],
        ];
    }

    /**
     * @param  array{min: int, max: int}  $price
     */
    private function intro(Category $category, array $price, int $total): string
    {
        $description = $category->translated('description');

        if (filled($description)) {
            return strip_tags($description);
        }

        $store = config('app.name');
        $name = $category->translated('name');

        if ($total === 0) {
            return __(':name products will be available at :store soon. Stay tuned for the latest prices in Bangladesh.', ['name' => $name, 'store' => $store]);
        }

        return __(':name price in Bangladesh at :store starts from only BDT :min and can reach up to BDT :max based on brand and specifications. Discover :count+ :lower products with official warranty, attractive discounts and fast delivery across Bangladesh. :store is your trusted destination to compare and buy :name at the best price.', [
            'name' => $name,
            'store' => $store,
            'min' => Money::format($price['min']),
            'max' => Money::format($price['max']),
            'count' => Localization::digits(number_format($total)),
            'lower' => Str::lower($name),
        ]);
    }

    /**
     * @return list<array{label: string, url: ?string}>
     */
    private function breadcrumbs(Category $category): array
    {
        $ancestors = Category::query()
            ->whereKey($category->ancestorIds())
            ->orderBy('depth')
            ->get(['id', 'name', 'name_bn', 'slug'])
            ->map(fn (Category $ancestor): array => ['label' => $ancestor->translated('name'), 'url' => route('store.categories.show', $ancestor->slug)])
            ->all();

        return [
            ['label' => __('Home'), 'url' => route('store.home')],
            ...$ancestors,
            ['label' => $category->translated('name'), 'url' => null],
        ];
    }

    /**
     * @return list<array{name: string, url: string}>
     */
    private function subcategories(Category $category): array
    {
        return $category->children()
            ->active()
            ->get(['id', 'name', 'name_bn', 'slug'])
            ->map(fn (Category $child): array => ['name' => $child->translated('name'), 'url' => route('store.categories.show', $child->slug)])
            ->all();
    }

    /**
     * Sidebar groups after "Price Range": availability first, then brands and specifications.
     * A group opens by default when it is the availability list or already has a selection.
     *
     * @param  array{availability: list<FacetOption>, brands: list<FacetOption>, specs: list<array{id: int, slug: string, name: string, by_option: bool, options: list<FacetOption>}>}  $facets
     * @param  Filters  $filters
     * @return list<FilterGroup>
     */
    public function filterGroups(array $facets, array $filters): array
    {
        $groups = [
            $this->group('stock[]', 'availability', __('Availability'), $facets['availability'], $filters['stock'], alwaysOpen: true),
        ];

        if ($facets['brands'] !== []) {
            $groups[] = $this->group('brand[]', 'brands', __('Brands'), $facets['brands'], $filters['brands']);
        }

        foreach ($facets['specs'] as $spec) {
            $groups[] = $this->group("spec[{$spec['slug']}][]", $spec['slug'], $spec['name'], $spec['options'], $filters['specs'][$spec['slug']] ?? []);
        }

        return $groups;
    }

    /**
     * @param  list<FacetOption>  $options
     * @param  list<string>  $selected
     * @return FilterGroup
     */
    private function group(string $input, string $key, string $name, array $options, array $selected, bool $alwaysOpen = false): array
    {
        $options = array_map(fn (array $option): array => [
            'value' => $option['value'],
            'label' => $option['label'],
            'count' => $option['count'],
            'checked' => in_array($option['value'], $selected, true),
        ], $options);

        $hasSelection = in_array(true, array_column($options, 'checked'), true);

        return [
            'key' => $key,
            'name' => $name,
            'input' => $input,
            'open' => $alwaysOpen || $hasSelection,
            'options' => $options,
        ];
    }
}
