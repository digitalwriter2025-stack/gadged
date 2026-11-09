<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductFilterService;
use App\Support\Storefront\ProductCardPresenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchSuggestionController extends Controller
{
    public const LIMIT = 6;

    public const CATEGORY_LIMIT = 3;

    /**
     * Return the top matching published products and active categories for the header search box.
     */
    public function __invoke(Request $request, ProductFilterService $filterService, ProductCardPresenter $cards): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['data' => []]);
        }

        $productQuery = $filterService->searchQuery($term);

        $products = $filterService->orderByRelevance((clone $productQuery)->forCard(), $term)
            ->orderByDesc('products.is_featured')
            ->limit(self::LIMIT)
            ->get();

        $pattern = '%'.addcslashes(mb_substr($term, 0, 100), '\%_').'%';

        $categories = Category::query()
            ->active()
            ->where(fn (Builder $query) => $query->where('name', 'like', $pattern)->orWhere('name_bn', 'like', $pattern))
            ->ordered()
            ->limit(self::CATEGORY_LIMIT)
            ->get(['id', 'name', 'name_bn', 'slug']);

        return response()->json([
            'data' => $products->map(function (Product $product) use ($cards): array {
                $card = $cards->present($product);

                return [
                    'name' => $card['name'],
                    'url' => $card['url'],
                    'image' => $cards->image($product, 'thumb'),
                    'brand' => $product->brand?->name,
                    'price' => $card['price'],
                    'regular_price' => $card['regular_price'],
                    'discount_percent' => $card['regular_price'] !== null && $product->discount_percent > 0 ? $product->discount_percent : null,
                    'in_stock' => $card['purchasable'],
                    'stock_label' => $card['stock_label'],
                ];
            })->values(),
            'categories' => $categories->map(fn (Category $category): array => [
                'name' => $category->translated('name'),
                'url' => route('store.categories.show', $category->slug),
            ])->values(),
            'meta' => [
                'total' => $productQuery->count(),
                'search_url' => route('store.search', ['q' => $term]),
            ],
        ]);
    }
}
