<?php

namespace App\Support\Storefront;

use App\Enums\PaymentMethod;
use App\Models\District;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShippingMethod;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Support\CartLine;
use App\Support\Localization;
use App\Support\Money;

/**
 * Turns the session cart and placed orders into the plain arrays rendered by
 * store/checkout/*.blade.php.
 */
class CheckoutPresenter
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CheckoutService $checkout,
        private readonly ProductCardPresenter $cards,
        private readonly StoreLayout $layout,
    ) {}

    /**
     * @return array{
     *     lines: list<array{variant_id: int, name: string, url: string, image: string, sku: string, quantity: int, unit_price: string, regular_price: ?string, line_total: string, is_deal: bool}>,
     *     itemCount: int,
     *     maxQuantity: int,
     *     subtotal: string,
     *     subtotalAmount: string,
     *     minimumOrder: ?string,
     *     districts: list<array{id: int, name: string}>,
     *     shippingMethods: list<array{id: int, name: string, description: ?string, estimate: ?string, shipping_amount: string, charge_label: string}>,
     *     paymentMethods: list<array{value: string, label: string}>,
     *     currencySymbol: string,
     *     breadcrumbs: list<array{label: string, url: ?string}>,
     *     seo: array{title: string, description: string}
     * }
     */
    public function present(): array
    {
        $lines = $this->cart->lines();
        $subtotal = $this->cart->subtotal($lines);
        $minimum = $this->checkout->minimumOrderAmount();

        return [
            'lines' => $lines->map(fn (CartLine $line): array => [
                'variant_id' => $line->variant->id,
                'name' => $line->product->isVariable() && filled($line->variant->name)
                    ? "{$line->product->translated('name')} ({$line->variant->name})"
                    : $line->product->translated('name'),
                'url' => route('store.products.show', $line->product->slug),
                'image' => $this->cards->image($line->product, 'thumb'),
                'sku' => $line->variant->sku,
                'quantity' => $line->quantity,
                'unit_price' => Money::format($line->unitPrice),
                'regular_price' => Money::isPositive($line->unitDiscount()) ? Money::format($line->regularPrice) : null,
                'line_total' => Money::format($line->lineTotal()),
                'is_deal' => $line->flashSaleItem !== null,
            ])->all(),
            'itemCount' => $lines->sum(fn (CartLine $line): int => $line->quantity),
            'maxQuantity' => $this->cart->maxQuantity(),
            'subtotal' => Money::format($subtotal),
            'subtotalAmount' => $subtotal,
            'minimumOrder' => $lines->isNotEmpty() && Money::compare($subtotal, $minimum) < 0 ? Money::format($minimum) : null,
            'districts' => District::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'name_bn'])
                ->map(fn (District $district): array => ['id' => $district->id, 'name' => $district->translated('name')])
                ->all(),
            'shippingMethods' => $this->shippingMethods($subtotal),
            'paymentMethods' => array_map(fn (PaymentMethod $method): array => [
                'value' => $method->value,
                'label' => $method->getLabel(),
            ], $this->checkout->paymentMethods()),
            'currencySymbol' => (string) config('shop.currency.symbol'),
            'breadcrumbs' => [
                ['label' => __('Home'), 'url' => route('store.home')],
                ['label' => __('Checkout'), 'url' => null],
            ],
            'seo' => [
                'title' => __('Checkout'),
                'description' => __('Review your cart and place your order.'),
            ],
        ];
    }

    /**
     * @return array{
     *     order: array{number: string, date: string, customer_name: string, customer_phone: string, customer_email: ?string, address: string, payment_method: string, payment_status: string, status: string, shipping_method: ?string, note: ?string},
     *     items: list<array{name: string, sku: ?string, warranty: ?string, quantity: int, unit_price: string, line_total: string}>,
     *     totals: array{subtotal: string, discount: ?string, coupon_code: ?string, shipping: string, tax: ?string, grand_total: string, paid: string, due: string},
     *     site: array{name: string, logo: ?string},
     *     contact: array{hotline: ?string, hotline_tel: ?string, email: ?string, address: ?string, whatsapp: ?string, map_url: ?string, complain_url: ?string},
     *     seo: array{title: string, description: string}
     * }
     */
    public function presentPlaced(Order $order): array
    {
        $order->loadMissing('items');

        return [
            'order' => [
                'number' => $order->order_number,
                'date' => Localization::date($order->created_at->timezone(config('app.display_timezone')), 'd M Y, h:i A'),
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'customer_email' => $order->customer_email,
                'address' => collect([$order->shipping_address, $order->shipping_area, $order->shipping_district])->filter()->implode(', '),
                'payment_method' => $order->payment_method->getLabel(),
                'payment_status' => $order->payment_status->getLabel(),
                'status' => $order->status->getLabel(),
                'shipping_method' => $order->shipping_method_name ?? $order->shipping_zone_name,
                'note' => $order->customer_note,
            ],
            'items' => $order->items->map(fn (OrderItem $item): array => [
                'name' => $item->variant_name ? "{$item->product_name} ({$item->variant_name})" : $item->product_name,
                'sku' => $item->sku,
                'warranty' => $item->warranty,
                'quantity' => $item->quantity,
                'unit_price' => Money::format($item->unit_price),
                'line_total' => Money::format($item->line_total),
            ])->all(),
            'totals' => [
                'subtotal' => Money::format($order->subtotal),
                'discount' => Money::isPositive($order->discount_total) ? '-'.Money::format($order->discount_total) : null,
                'coupon_code' => $order->coupon_code,
                'shipping' => Money::isZero($order->shipping_total) ? __('Free') : Money::format($order->shipping_total),
                'tax' => Money::isPositive($order->tax_total) ? Money::format($order->tax_total) : null,
                'grand_total' => Money::format($order->grand_total),
                'paid' => Money::format($order->paid_total),
                'due' => Money::format(Money::max(Money::sub($order->grand_total, $order->paid_total), '0')),
            ],
            'site' => $this->layout->site(),
            'contact' => $this->layout->contact(),
            'seo' => [
                'title' => __('Thank you for your order'),
                'description' => __('Order :number has been placed.', ['number' => $order->order_number]),
            ],
        ];
    }

    /**
     * Active delivery methods with the charge for the current subtotal, for the
     * live total on the checkout page (the order total is recalculated on submit).
     *
     * @return list<array{id: int, name: string, description: ?string, estimate: ?string, shipping_amount: string, charge_label: string}>
     */
    private function shippingMethods(string $subtotal): array
    {
        return $this->checkout->shippingMethods()
            ->map(function (ShippingMethod $method) use ($subtotal): array {
                $charge = $method->chargeFor($subtotal);

                return [
                    'id' => $method->id,
                    'name' => $method->translated('name'),
                    'description' => $method->translated('description'),
                    'estimate' => $method->translated('delivery_estimate'),
                    'shipping_amount' => $charge,
                    'charge_label' => Money::isZero($charge) ? __('Free') : Money::format($charge),
                ];
            })
            ->values()
            ->all();
    }
}
