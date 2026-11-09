<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\ProductListingRequest;
use App\Support\Storefront\ProductCollectionPagePresenter;
use Illuminate\Contracts\View\View;

class ComboController extends Controller
{
    /**
     * Show combo (bundle) products, with listing filters.
     */
    public function __invoke(ProductListingRequest $request, ProductCollectionPagePresenter $presenter): View
    {
        return view('store.category', $presenter->combos($request->filters()));
    }
}
