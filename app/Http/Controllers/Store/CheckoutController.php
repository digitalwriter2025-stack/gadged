<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\CheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\GoogleTag;
use App\Services\MetaPixel;
use App\Support\Storefront\CheckoutPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * Session key listing the order numbers placed in this session; only those
     * thank-you pages can be opened, so order details never leak by guessing.
     */
    public const PLACED_ORDERS_KEY = 'placed_orders';

    /**
     * Show the cart together with the delivery form.
     */
    public function show(CheckoutPresenter $presenter, CartService $cart, MetaPixel $metaPixel, GoogleTag $googleTag): View
    {
        if ($metaPixel->enabled() || $googleTag->enabled()) {
            $lines = $cart->lines();

            $metaPixel->initiateCheckout($lines);
            $googleTag->beginCheckout($lines);
        }

        return view('store.checkout.show', $presenter->present());
    }

    /**
     * Place the order and move on to the thank-you page.
     */
    public function store(CheckoutRequest $request, CheckoutService $checkout, MetaPixel $metaPixel, GoogleTag $googleTag): RedirectResponse
    {
        $order = $checkout->placeOrder($request->details(), $request->user(), $request->ip(), $request->userAgent());

        $request->session()->push(self::PLACED_ORDERS_KEY, $order->order_number);

        $metaPixel->purchase($order);
        $googleTag->purchase($order);

        return redirect()->route('store.checkout.thank-you', $order);
    }

    /**
     * Confirmation shown right after an order is placed.
     */
    public function thankYou(Request $request, Order $order, CheckoutPresenter $presenter): View
    {
        abort_unless(in_array($order->order_number, (array) $request->session()->get(self::PLACED_ORDERS_KEY, []), true), 404);

        return view('store.checkout.thank-you', $presenter->presentPlaced($order));
    }
}
