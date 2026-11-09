<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Product;
use App\Support\Storefront\OfferPresenter;
use App\Support\Storefront\ProductCardPresenter;
use Illuminate\Contracts\View\View;

class OfferController extends Controller
{
    /**
     * Show every running campaign.
     */
    public function index(OfferPresenter $presenter): View
    {
        $offers = Offer::query()->running()->with('media')->get()
            ->map(fn (Offer $offer): array => $presenter->present($offer))
            ->all();

        return view('store.offers.index', [
            'heading' => __('Campaigns'),
            'intro' => __('Running campaigns and limited-time discounts on smartwatches, earbuds, speakers, power banks, chargers and smartphones.'),
            'offers' => $offers,
            'breadcrumbs' => [
                ['label' => __('Home'), 'url' => route('store.home')],
                ['label' => __('Campaigns'), 'url' => null],
            ],
        ]);
    }

    /**
     * Show a running offer with its products.
     */
    public function show(Offer $offer, OfferPresenter $presenter, ProductCardPresenter $cards): View
    {
        abort_unless($offer->isRunning(), 404);

        $products = $offer->products()
            ->published()
            ->forCard()
            ->with(ProductCardPresenter::relations())
            ->get()
            ->map(fn (Product $product): array => $cards->present($product))
            ->all();

        return view('store.offers.show', [
            'offer' => $presenter->present($offer),
            'description' => $offer->translated('description'),
            'products' => $products,
            'breadcrumbs' => [
                ['label' => __('Home'), 'url' => route('store.home')],
                ['label' => __('Campaigns'), 'url' => route('store.offers.index')],
                ['label' => $offer->translated('title'), 'url' => null],
            ],
        ]);
    }
}
