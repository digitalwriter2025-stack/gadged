<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\ProductListingRequest;
use App\Models\Category;
use App\Support\Storefront\CategoryPagePresenter;
use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    /**
     * Show a category's product listing with filters.
     */
    public function __invoke(ProductListingRequest $request, Category $category, CategoryPagePresenter $presenter): View
    {
        abort_unless($category->is_active, 404);

        return view('store.category', $presenter->present($category, $request->filters()));
    }
}
