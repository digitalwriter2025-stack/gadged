<?php

namespace App\Support\Storefront;

use App\Enums\PaymentMethod;
use App\Models\District;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Support\CartLine;
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
     *     districts: list<array{id: int, name: string, shipping_amount: ?string, delivery_time: ?string}>,
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
                    ? "{$line->product->name} ({$line->variant->name})"
                    : $line->product->name,
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
            'districts' => $this->districts($subtotal),
            'paymentMethods' => array_map(fn (PaymentMethod $method): array => [
                'value' => $method->value,
                'label' => $method->getLabel(),
            ], $this->checkout->paymentMethods()),
            'currencySymbol' => (string) config('shop.currency.symbol'),
            'breadcrumbs' => [
                ['label' => 'Home', 'url' => route('store.home')],
                ['label' => 'Checkout', 'url' => null],
            ],
            'seo' => [
                'title' => 'Checkout',
                'description' => 'Review your cart and place your order.',
            ],
        ];
    }

    /**
     * @return array{
     *     order: array{number: string, date: string, customer_name: string, customer_phone: string, customer_email: ?string, address: string, payment_method: string, note: ?string},
     *     items: list<array{name: string, quantity: int, unit_price: string, line_total: string}>,
     *     totals: array{subtotal: string, shipping: string, grand_total: string},
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
                'date' => $order->created_at->timezone(config('app.display_timezone'))->format('d M Y, h:i A'),
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'customer_email' => $order->customer_email,
                'address' => collect([$order->shipping_address, $order->shipping_area, $order->shipping_district])->filter()->implode(', '),
                'payment_method' => $order->payment_method->getLabel(),
                'note' => $order->customer_note,
            ],
            'items' => $order->items->map(fn (OrderItem $item): array => [
                'name' => $item->variant_name ? "{$item->product_name} ({$item->variant_name})" : $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => Money::format($item->unit_price),
                'line_total' => Money::format($item->line_total),
            ])->all(),
            'totals' => [
                'subtotal' => Money::format($order->subtotal),
                'shipping' => Money::isZero($order->shipping_total) ? 'Free' : Money::format($order->shipping_total),
                'grand_total' => Money::format($order->grand_total),
            ],
            'contact' => $this->layout->contact(),
            'seo' => [
                'title' => 'Thank you for your order',
                'description' => "Order {$order->order_number} has been placed.",
            ],
        ];
    }

    /**
     * Active districts with the delivery charge for the current subtotal, for the
     * live total on the checkout page (the order total is recalculated on submit).
     *
     * @return list<array{id: int, name: string, shipping_amount: ?string, delivery_time: ?string}>
     */
    private function districts(string $subtotal): array
    {
        $rates = $this->checkout->shippingRates($this->checkout->shippingMethod());

        return District::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'shipping_zone_id'])
            ->map(function (District $district) use ($rates, $subtotal): array {
                $rate = $district->shipping_zone_id === null ? null : $rates->get($district->shipping_zone_id);

                return [
                    'id' => $district->id,
                    'name' => $district->name,
                    'shipping_amount' => $rate?->chargeFor($subtotal),
                    'delivery_time' => $rate?->min_delivery_days && $rate->max_delivery_days
                        ? "{$rate->zone->name}: {$rate->min_delivery_days}-{$rate->max_delivery_days} days"
                        : $rate?->zone->name,
                ];
            })
            ->all();
    }
}
