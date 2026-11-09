<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\TrackOrderRequest;
use App\Models\Order;
use App\Support\Storefront\OrderTrackingPresenter;
use App\Support\Storefront\StoreLayout;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    /**
     * Session key listing the order numbers this visitor has verified with
     * order number + mobile number; only those can be shown by URL.
     */
    public const TRACKED_ORDERS_KEY = 'tracked_orders';

    /**
     * Show the lookup form, or the progress of an order this session may see.
     */
    public function show(Request $request, OrderTrackingPresenter $presenter, StoreLayout $layout): View
    {
        $orderNumber = $request->query('order');
        $order = null;

        if (is_string($orderNumber) && $this->mayView($request, $orderNumber)) {
            $order = Order::query()->where('order_number', $orderNumber)->first();
        }

        return view('store.orders.track', [
            'tracking' => $order === null ? null : $presenter->present($order),
            'contact' => $layout->contact(),
            'breadcrumbs' => [
                ['label' => __('Home'), 'url' => route('store.home')],
                ['label' => __('Track Order'), 'url' => null],
            ],
        ]);
    }

    /**
     * Look up an order by its number and the checkout mobile number.
     */
    public function lookup(TrackOrderRequest $request): RedirectResponse
    {
        $order = Order::query()
            ->where('order_number', $request->validated('order_number'))
            ->where('customer_phone', $request->validated('phone'))
            ->first(['id', 'order_number']);

        if ($order === null) {
            return back()
                ->withInput()
                ->withErrors(['order_number' => __('We could not find an order with this order number and mobile number. Please check both and try again.')]);
        }

        $request->session()->push(self::TRACKED_ORDERS_KEY, $order->order_number);

        return redirect()->route('store.orders.track', ['order' => $order->order_number]);
    }

    private function mayView(Request $request, string $orderNumber): bool
    {
        $allowed = [
            ...(array) $request->session()->get(self::TRACKED_ORDERS_KEY, []),
            ...(array) $request->session()->get(CheckoutController::PLACED_ORDERS_KEY, []),
        ];

        return in_array($orderNumber, $allowed, true);
    }
}
