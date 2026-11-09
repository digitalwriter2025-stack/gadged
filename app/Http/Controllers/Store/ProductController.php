<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\GoogleTag;
use App\Services\MetaPixel;
use App\Support\Storefront\ProductPagePresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Show a single product page.
     */
    public function __invoke(Request $request, Product $product, ProductPagePresenter $presenter, MetaPixel $metaPixel, GoogleTag $googleTag): View
    {
        abort_unless($product->isPublished(), 404);

        // Query-builder increment: no model events, timestamps or search re-indexing.
        Product::query()->whereKey($product->id)->toBase()->increment('view_count');

        $page = $presenter->present($product, $request->user());

        $metaPixel->viewContent($product, $page['pricing']['amount'], $page['product']['category']);
        $googleTag->viewItem($product, $page['pricing']['amount'], $page['product']['category'], $page['product']['brand']);

        return view('store.product', $page);
    }
}
