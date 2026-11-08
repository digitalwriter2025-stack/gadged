<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\ProductListingRequest;
use App\Services\MetaPixel;
use App\Support\Storefront\SearchPagePresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SearchController extends Controller
{
    /**
     * Show catalog-wide product search results with filters.
     */
    public function __invoke(ProductListingRequest $request, SearchPagePresenter $presenter, MetaPixel $metaPixel): View|RedirectResponse
    {
        $filters = $request->filters();

        if ($filters['q'] === null) {
            return redirect()->route('store.home');
        }

        // Paging through the same results is not a new search.
        if ((int) $request->query('page', '1') <= 1) {
            $metaPixel->search($filters['q']);
        }

        return view('store.category', $presenter->present($filters['q'], $filters));
    }
}
