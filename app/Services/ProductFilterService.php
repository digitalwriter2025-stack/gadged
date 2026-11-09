<?php

namespace App\Services;

use App\Enums\SpecificationInputType;
use App\Enums\StockStatus;
use App\Models\Product;
use App\Models\Specification;
use App\Models\SpecificationOption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Filtering, sorting and facet data for storefront product listings.
 *
 * Facets describe everything a listing *could* be filtered by (with product
 * counts for the unfiltered listing); apply() narrows a query by the shopper's
 * selection, resolving slugs through those facets so unknown values are ignored.
 *
 * @phpstan-type Filters array{q: ?string, sort: string, stock: list<string>, min: ?int, max: ?int, brands: list<string>, specs: array<string, list<string>>}
 * @phpstan-type FacetOption array{id: ?int, value: string, label: string, count: int}
 * @phpstan-type Facets array{
 *     availability: list<FacetOption>,
 *     price: array{min: int, max: int},
 *     brands: list<FacetOption>,
 *     specs: list<array{id: int, slug: string, name: string, by_option: bool, options: list<FacetOption>}>,
 *     total: int
 * }
 */
class ProductFilterService
{
    public const PER_PAGE = 20;

    public const DEFAULT_SORT = 'default';

    /**
     * Upper bound on Meilisearch hits per search (its default maxTotalHits).
     */
    public const MAX_SEARCH_HITS = 1000;

    /**
     * @var array<string, list<int>|null>
     */
    private array $searchIdCache = [];

    /**
     * @var array<string, string>
     */
    public const SORTS = [
        'default' => 'Default',
        'price_asc' => 'Price (Low > High)',
        'price_desc' => 'Price (High > Low)',
        'newest' => 'Newest First',
        'popular' => 'Best Selling',
        'rating' => 'Top Rated',
    ];

    /**
     * SORTS with the labels in the storefront language, for the sort dropdown.
     *
     * @return array<string, string>
     */
    public static function sortOptions(): array
    {
        return array_map(fn (string $label): string => __($label), self::SORTS);
    }

    /**
     * Published products listed in any of the given categories.
     *
     * @param  list<int>  $categoryIds
     * @return Builder<Product>
     */
    public function baseQuery(array $categoryIds): Builder
    {
        return Product::query()->published()->inCategories($categoryIds);
    }

    /**
     * Published products matching the search term. Uses Meilisearch (typo tolerant,
     * also matches brand, category, Bangla name and keywords) when it is the Scout
     * driver and reachable; otherwise a LIKE match on name, SKU and model.
     *
     * @return Builder<Product>
     */
    public function searchQuery(string $term): Builder
    {
        $ids = $this->searchIds($term);

        if ($ids !== null) {
            return Product::query()->published()->whereIn('products.id', $ids);
        }

        $pattern = '%'.addcslashes(mb_substr($term, 0, 100), '\\%_').'%';

        return Product::query()->published()->where(fn (Builder $query) => $query
            ->where('products.name', 'like', $pattern)
            ->orWhere('products.name_bn', 'like', $pattern)
            ->orWhere('products.sku', 'like', $pattern)
            ->orWhere('products.model', 'like', $pattern));
    }

    /**
     * Order a searchQuery() result by search-engine relevance, best match first.
     * A no-op when the LIKE fallback is in use.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function orderByRelevance(Builder $query, string $term): Builder
    {
        $ids = $this->searchIds($term);

        if ($ids === null || $ids === []) {
            return $query;
        }

        return $query->orderByRaw('FIELD(products.id, '.implode(',', $ids).')');
    }

    /**
     * Relevance-ordered product ids from Meilisearch, or null when it is not the
     * Scout driver or cannot be reached. Memoised so one request queries it once per term.
     *
     * @return list<int>|null
     */
    private function searchIds(string $term): ?array
    {
        if (config('scout.driver') !== 'meilisearch') {
            return null;
        }

        $term = mb_substr($term, 0, 100);

        if (array_key_exists($term, $this->searchIdCache)) {
            return $this->searchIdCache[$term];
        }

        try {
            $ids = Product::search($term)->take(self::MAX_SEARCH_HITS)->keys()
                ->map(fn (int|string $id): int => (int) $id)
                ->values()
                ->all();
        } catch (Throwable $exception) {
            report($exception);
            $ids = null;
        }

        return $this->searchIdCache[$term] = $ids;
    }

    /**
     * @param  list<int>  $categoryIds
     * @return Facets
     */
    public function facets(array $categoryIds): array
    {
        return $this->facetsFor($this->baseQuery($categoryIds), $this->specifications($categoryIds));
    }

    /**
     * Facets for a catalog-wide search. Specifications are mapped per category, so none are offered.
     *
     * @return Facets
     */
    public function searchFacets(string $term): array
    {
        return $this->facetsFor($this->searchQuery($term), []);
    }

    /**
     * Facets for a cross-category collection such as trending products or combos.
     * Specifications are mapped per category, so none are offered.
     *
     * @param  Builder<Product>  $base
     * @return Facets
     */
    public function collectionFacets(Builder $base): array
    {
        return $this->facetsFor($base, []);
    }

    /**
     * @param  Builder<Product>  $base
     * @param  list<array{id: int, slug: string, name: string, by_option: bool, options: list<FacetOption>}>  $specs
     * @return Facets
     */
    private function facetsFor(Builder $base, array $specs): array
    {
        $price = (clone $base)->toBase()
            ->selectRaw('count(*) as total, min(products.min_price) as low, max(products.max_price) as high')
            ->first();

        return [
            'availability' => $this->availability(clone $base),
            'price' => ['min' => (int) floor((float) $price->low), 'max' => (int) ceil((float) $price->high)],
            'brands' => $this->brands(clone $base),
            'specs' => $specs,
            'total' => (int) $price->total,
        ];
    }

    /**
     * @param  Builder<Product>  $query
     * @param  Filters  $filters
     * @param  Facets  $facets
     * @return Builder<Product>
     */
    public function apply(Builder $query, array $filters, array $facets): Builder
    {
        if ($filters['q'] !== null) {
            $pattern = '%'.addcslashes($filters['q'], '\\%_').'%';
            $query->where(fn (Builder $query) => $query->where('products.name', 'like', $pattern)->orWhere('products.name_bn', 'like', $pattern));
        }

        if ($filters['stock'] !== []) {
            $query->whereIn('products.stock_status', $filters['stock']);
        }

        if ($filters['min'] !== null) {
            $query->where('products.min_price', '>=', $filters['min']);
        }

        if ($filters['max'] !== null) {
            $query->where('products.min_price', '<=', $filters['max']);
        }

        $brandIds = collect($facets['brands'])->whereIn('value', $filters['brands'])->pluck('id')->all();

        if ($brandIds !== []) {
            $query->whereIn('products.brand_id', $brandIds);
        }

        foreach ($facets['specs'] as $spec) {
            $selected = collect($spec['options'])->whereIn('value', $filters['specs'][$spec['slug']] ?? []);

            if ($selected->isEmpty()) {
                continue;
            }

            $query->whereExists(fn (QueryBuilder $sub) => $sub->from('product_specification_values')
                ->whereColumn('product_specification_values.product_id', 'products.id')
                ->where('product_specification_values.specification_id', $spec['id'])
                ->when(
                    $spec['by_option'],
                    fn (QueryBuilder $values) => $values->whereIn('specification_option_id', $selected->pluck('id')->all()),
                    fn (QueryBuilder $values) => $values->whereIn('value_numeric', $selected->pluck('value')->all()),
                ));
        }

        return $this->sort($query, $filters['sort']);
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function sort(Builder $query, string $sort): Builder
    {
        match ($sort) {
            'price_asc' => $query->orderBy('products.min_price'),
            'price_desc' => $query->orderByDesc('products.min_price'),
            'newest' => $query->orderByDesc('products.published_at'),
            'popular' => $query->orderByDesc('products.sales_count'),
            'rating' => $query->orderByDesc('products.rating_avg')->orderByDesc('products.rating_count'),
            default => $query->orderByDesc('products.is_featured')->orderByDesc('products.sales_count'),
        };

        return $query->orderByDesc('products.id');
    }

    /**
     * Every stock status, so the filter list stays stable even when a status has no products.
     *
     * @param  Builder<Product>  $base
     * @return list<FacetOption>
     */
    private function availability(Builder $base): array
    {
        $counts = $base->toBase()
            ->selectRaw('products.stock_status, count(*) as aggregate')
            ->groupBy('products.stock_status')
            ->pluck('aggregate', 'stock_status');

        return array_map(fn (StockStatus $status): array => [
            'id' => null,
            'value' => $status->value,
            'label' => $status->getLabel(),
            'count' => (int) ($counts[$status->value] ?? 0),
        ], StockStatus::cases());
    }

    /**
     * @param  Builder<Product>  $base
     * @return list<FacetOption>
     */
    private function brands(Builder $base): array
    {
        return $base->toBase()
            ->join('brands', 'brands.id', '=', 'products.brand_id')
            ->where('brands.is_active', true)
            ->whereNull('brands.deleted_at')
            ->groupBy('brands.id', 'brands.name', 'brands.slug')
            ->orderBy('brands.name')
            ->get(['brands.id', 'brands.name', 'brands.slug', DB::raw('count(*) as aggregate')])
            ->map(fn (object $brand): array => [
                'id' => (int) $brand->id,
                'value' => $brand->slug,
                'label' => $brand->name,
                'count' => (int) $brand->aggregate,
            ])
            ->all();
    }

    /**
     * Filterable specifications mapped to the category subtree, with the values products actually have.
     * Choice specifications filter by option; number specifications filter by exact value.
     *
     * @param  list<int>  $categoryIds
     * @return list<array{id: int, slug: string, name: string, by_option: bool, options: list<FacetOption>}>
     */
    private function specifications(array $categoryIds): array
    {
        $positions = DB::table('category_specification')
            ->whereIn('category_id', $categoryIds)
            ->where('is_filterable', true)
            ->groupBy('specification_id')
            ->selectRaw('specification_id, min(sort_order) as position')
            ->pluck('position', 'specification_id');

        if ($positions->isEmpty()) {
            return [];
        }

        $specifications = Specification::query()
            ->whereKey($positions->keys()->all())
            ->where('is_filterable', true)
            ->whereIn('input_type', [SpecificationInputType::Select, SpecificationInputType::MultiSelect, SpecificationInputType::Number])
            ->get(['id', 'name', 'name_bn', 'slug', 'unit', 'input_type'])
            ->sortBy(fn (Specification $specification): array => [(int) $positions[$specification->id], $specification->id]);

        if ($specifications->isEmpty()) {
            return [];
        }

        $rows = DB::table('product_specification_values')
            ->whereIn('specification_id', $specifications->modelKeys())
            ->whereIn('product_id', $this->baseQuery($categoryIds)->toBase()->select('products.id'))
            ->groupBy('specification_id', 'specification_option_id', 'value_numeric')
            ->get(['specification_id', 'specification_option_id', 'value_numeric', DB::raw('count(distinct product_id) as aggregate')])
            ->groupBy('specification_id');

        $options = SpecificationOption::query()
            ->whereKey($rows->flatten(1)->pluck('specification_option_id')->filter()->unique()->values()->all())
            ->get(['id', 'value', 'value_bn', 'slug', 'sort_order'])
            ->keyBy('id');

        return $specifications
            ->map(function (Specification $specification) use ($rows, $options): array {
                $values = $rows->get($specification->id, collect());
                $byOption = $specification->hasOptions();

                $facetOptions = $byOption
                    ? $values->filter(fn (object $row): bool => $options->has($row->specification_option_id))
                        ->sortBy(fn (object $row): array => [$options[$row->specification_option_id]->sort_order, $options[$row->specification_option_id]->value])
                        ->map(fn (object $row): array => [
                            'id' => (int) $row->specification_option_id,
                            'value' => $options[$row->specification_option_id]->slug,
                            'label' => $options[$row->specification_option_id]->translated('value'),
                            'count' => (int) $row->aggregate,
                        ])
                    : $values->whereNotNull('value_numeric')
                        ->sortBy(fn (object $row): float => (float) $row->value_numeric)
                        ->map(fn (object $row): array => [
                            'id' => null,
                            'value' => $this->number((string) $row->value_numeric),
                            'label' => trim($this->number((string) $row->value_numeric).' '.$specification->unit),
                            'count' => (int) $row->aggregate,
                        ]);

                return [
                    'id' => $specification->id,
                    'slug' => $specification->slug,
                    'name' => $specification->translated('name'),
                    'by_option' => $byOption,
                    'options' => $facetOptions->values()->all(),
                ];
            })
            ->filter(fn (array $spec): bool => $spec['options'] !== [])
            ->values()
            ->all();
    }

    /**
     * "16.0000" → "16", "15.6000" → "15.6".
     */
    private function number(string $value): string
    {
        return str_contains($value, '.') ? rtrim(rtrim($value, '0'), '.') : $value;
    }
}
