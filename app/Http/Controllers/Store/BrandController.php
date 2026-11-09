<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class BrandController extends Controller
{
    /**
     * Show every active brand that has products on sale.
     */
    public function __invoke(): View
    {
        $brands = Brand::query()
            ->active()
            ->whereHas('products', fn (Builder $products) => $products->published())
            ->withCount(['products' => fn (Builder $products) => $products->published()])
            ->with('media')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Brand $brand): array => [
                'name' => $brand->name,
                'url' => route('store.search', ['q' => $brand->name]),
                'image' => $brand->getFirstMedia('logo')?->getAvailableUrl(['card']),
                'product_count' => $brand->products_count,
            ]);

        return view('store.brands', [
            'brands' => $brands,
            'breadcrumbs' => [
                ['label' => __('Home'), 'url' => route('store.home')],
                ['label' => __('Brands'), 'url' => null],
            ],
        ]);
    }
}
