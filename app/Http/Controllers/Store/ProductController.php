<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\MetaPixel;
use App\Support\Storefront\ProductPagePresenter;
use Illuminate\Contracts\View\View;

class ProductController extends Controller
{
    /**
     * Show a single product page.
     */
    public function __invoke(Product $product, ProductPagePresenter $presenter, MetaPixel $metaPixel): View
    {
        abort_unless($product->isPublished(), 404);

        // Query-builder increment: no model events, timestamps or search re-indexing.
        Product::query()->whereKey($product->id)->toBase()->increment('view_count');

        $page = $presenter->present($product);

        $metaPixel->viewContent($product, $page['pricing']['amount'], $page['product']['category']);

        return view('store.product', $page);
    }
}
