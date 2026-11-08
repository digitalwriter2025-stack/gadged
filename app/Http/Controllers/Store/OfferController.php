<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Product;
use App\Support\Storefront\OfferPresenter;
use App\Support\Storefront\ProductCardPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class OfferController extends Controller
{
    /**
     * Show every running offer.
     */
    public function index(OfferPresenter $presenter): View
    {
        return $this->listing('Offers', 'Latest offers and discounts on gadgets, laptops, PC components and more.', Offer::query(), $presenter);
    }

    /**
     * Show running offers flagged as mobile deals.
     */
    public function mobileDeals(OfferPresenter $presenter): View
    {
        return $this->listing('Mobile Deal', 'Special deals on smartphones, tablets and mobile accessories.', Offer::query()->mobileDeals(), $presenter);
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
            'description' => $offer->description,
            'products' => $products,
            'breadcrumbs' => [
                ['label' => 'Home', 'url' => route('store.home')],
                ['label' => 'Offers', 'url' => route('store.offers.index')],
                ['label' => $offer->title, 'url' => null],
            ],
        ]);
    }

    /**
     * @param  Builder<Offer>  $query
     */
    private function listing(string $heading, string $intro, Builder $query, OfferPresenter $presenter): View
    {
        $offers = $query->running()->with('media')->get()
            ->map(fn (Offer $offer): array => $presenter->present($offer))
            ->all();

        return view('store.offers.index', [
            'heading' => $heading,
            'intro' => $intro,
            'offers' => $offers,
            'breadcrumbs' => [
                ['label' => 'Home', 'url' => route('store.home')],
                ['label' => $heading, 'url' => null],
            ],
        ]);
    }
}
