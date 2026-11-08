<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductFilterService;
use App\Support\Storefront\ProductCardPresenter;
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

        $products = (clone $productQuery)
            ->forCard()
            ->orderByDesc('products.is_featured')
            ->limit(self::LIMIT)
            ->get();

        $categories = Category::query()
            ->active()
            ->where('name', 'like', '%'.addcslashes(mb_substr($term, 0, 100), '\%_').'%')
            ->ordered()
            ->limit(self::CATEGORY_LIMIT)
            ->get(['id', 'name', 'slug']);

        return response()->json([
            'data' => $products->map(fn (Product $product): array => [
                'name' => $product->name,
                'url' => route('store.products.show', $product->slug),
                'image' => $cards->image($product, 'thumb'),
                'price' => $cards->present($product)['price'],
            ])->values(),
            'categories' => $categories->map(fn (Category $category): array => [
                'name' => $category->name,
                'url' => route('store.categories.show', $category->slug),
            ])->values(),
            'meta' => [
                'total' => $productQuery->count(),
                'search_url' => route('store.search', ['q' => $term]),
            ],
        ]);
    }
}
