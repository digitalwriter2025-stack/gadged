<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    /**
     * Show a published CMS page (About Us, Privacy Policy, ...).
     */
    public function __invoke(Page $page): View
    {
        abort_unless($page->is_active, 404);

        return view('store.page', [
            'page' => $page,
            'breadcrumbs' => [
                ['label' => __('Home'), 'url' => route('store.home')],
                ['label' => $page->translated('title'), 'url' => null],
            ],
        ]);
    }
}
