<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Support\Storefront\HomePagePresenter;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Show the storefront homepage, built from the admin-managed home sections.
     */
    public function __invoke(HomePagePresenter $presenter): View
    {
        return view('store.home', ['sections' => $presenter->sections()]);
    }
}
