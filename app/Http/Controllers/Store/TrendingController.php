<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\ProductListingRequest;
use App\Support\Storefront\ProductCollectionPagePresenter;
use Illuminate\Contracts\View\View;

class TrendingController extends Controller
{
    /**
     * Show products flagged as trending, with listing filters.
     */
    public function __invoke(ProductListingRequest $request, ProductCollectionPagePresenter $presenter): View
    {
        return view('store.category', $presenter->trending($request->filters()));
    }
}
