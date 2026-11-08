<?php

namespace App\Services;

use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Exceptions\InsufficientStockException;
use App\Models\District;
use App\Models\FlashSaleProduct;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use App\Models\User;
use App\Support\CartLine;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Turns the session cart into a pending order: totals are recalculated from the
 * catalog, stock and flash sale quantities are taken in one transaction, and
 * the cart is emptied only after the order is saved.
 */
class CheckoutService
{
    public function __construct(
        private readonly CartService $cart,
        private readonly InventoryService $inventory,
        private readonly OrderNumberGenerator $orderNumbers,
        private readonly SettingsService $settings,
    ) {}

    /**
     * Payment methods enabled under orders.enabled_payment_methods (cash on delivery when none are).
     *
     * @return list<PaymentMethod>
     */
    public function paymentMethods(): array
    {
        $methods = collect((array) $this->settings->get('orders.enabled_payment_methods', []))
            ->map(fn (mixed $value): ?PaymentMethod => is_string($value) ? PaymentMethod::tryFrom($value) : null)
            ->filter()
            ->values()
            ->all();

        return $methods !== [] ? $methods : [PaymentMethod::CashOnDelivery];
    }

    /**
     * The delivery method used at checkout: shipping.default_method, otherwise the first active one.
     */
    public function shippingMethod(): ?ShippingMethod
    {
        $code = (string) $this->settings->get('shipping.default_method', 'standard');

        return ShippingMethod::query()->active()->where('code', $code)->first()
            ?? ShippingMethod::query()->active()->first();
    }

    /**
     * Active rates of the delivery method, keyed by shipping zone id.
     *
     * @return Collection<int, ShippingRate>
     */
    public function shippingRates(?ShippingMethod $method): Collection
    {
        if ($method === null) {
            return collect();
        }

        return ShippingRate::query()
            ->where('shipping_method_id', $method->id)
            ->where('is_active', true)
            ->whereHas('zone', fn (Builder $query) => $query->where('is_active', true))
            ->with('zone')
            ->get()
            ->keyBy('shipping_zone_id');
    }

    /**
     * Minimum cart subtotal from orders.min_order_amount.
     */
    public function minimumOrderAmount(): string
    {
        return Money::of($this->settings->get('orders.min_order_amount', '0'));
    }

    /**
     * @param  array{customer_name: string, customer_phone: string, customer_email: ?string, district_id: int, shipping_area: ?string, shipping_address: string, customer_note: ?string, payment_method: string}  $details
     *
     * @throws ValidationException when the cart is empty, below the minimum or out of stock
     */
    public function placeOrder(array $details, ?User $customer = null, ?string $ipAddress = null, ?string $userAgent = null): Order
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }

        $subtotal = $this->cart->subtotal($lines);
        $minimum = $this->minimumOrderAmount();

        if (Money::compare($subtotal, $minimum) < 0) {
            throw ValidationException::withMessages(['cart' => 'The minimum order amount is '.Money::format($minimum).'.']);
        }

        $district = District::query()->findOrFail($details['district_id']);
        $method = $this->shippingMethod();
        $rate = $district->shipping_zone_id === null ? null : $this->shippingRates($method)->get($district->shipping_zone_id);
        $shippingTotal = $rate?->chargeFor($subtotal) ?? '0.00';

        try {
            $order = DB::transaction(function () use ($details, $customer, $ipAddress, $userAgent, $lines, $subtotal, $district, $method, $rate, $shippingTotal): Order {
                $order = Order::query()->create([
                    'order_number' => $this->orderNumbers->next(),
                    'user_id' => $customer?->id,
                    'payment_method' => $details['payment_method'],
                    'source' => OrderSource::Web,
                    'customer_name' => $details['customer_name'],
                    'customer_phone' => $details['customer_phone'],
                    'customer_email' => $details['customer_email'] ?? null,
                    'shipping_address' => $details['shipping_address'],
                    'shipping_area' => $details['shipping_area'] ?? null,
                    'district_id' => $district->id,
                    'shipping_district' => $district->name,
                    'shipping_zone_id' => $rate?->shipping_zone_id,
                    'shipping_zone_name' => $rate?->zone->name,
                    'shipping_method_id' => $rate === null ? null : $method?->id,
                    'shipping_method_name' => $rate === null ? null : $method?->name,
                    'customer_note' => $details['customer_note'] ?? null,
                    'subtotal' => $subtotal,
                    'shipping_total' => $shippingTotal,
                    'grand_total' => Money::add($subtotal, $shippingTotal),
                    'currency' => config('shop.currency.code'),
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent === null ? null : Str::limit($userAgent, 497),
                ]);

                $order->items()->createMany($lines->map(fn (CartLine $line): array => [
                    'product_id' => $line->product->id,
                    'product_variant_id' => $line->variant->id,
                    'flash_sale_product_id' => $line->flashSaleItem?->id,
                    'product_name' => $line->product->name,
                    'sku' => $line->variant->sku,
                    'variant_name' => $line->product->isVariable() ? $line->variant->name : null,
                    'image_path' => $line->product->getFirstMedia('main')?->getPathRelativeToRoot(),
                    'warranty' => $line->product->warranty,
                    'regular_price' => $line->regularPrice,
                    'unit_price' => $line->unitPrice,
                    'unit_discount' => $line->unitDiscount(),
                    'quantity' => $line->quantity,
                    'line_total' => $line->lineTotal(),
                ])->all());

                $this->reserveFlashSaleQuantities($lines);

                $this->inventory->deductForOrder(
                    $lines->mapWithKeys(fn (CartLine $line): array => [$line->variant->id => $line->quantity])->all(),
                    $order,
                    $customer,
                );

                $order->statusHistories()->create([
                    'type' => 'status',
                    'to_status' => OrderStatus::Pending->value,
                    'note' => 'Order placed on the website.',
                    'user_id' => $customer?->id,
                ]);

                return $order;
            });
        } catch (InsufficientStockException $exception) {
            $line = $lines->first(fn (CartLine $line): bool => $line->variant->id === $exception->variantId);

            throw ValidationException::withMessages([
                'cart' => $exception->available > 0
                    ? "Only {$exception->available} of {$line?->product->name} left in stock. Please lower the quantity."
                    : "{$line?->product->name} just went out of stock. Please remove it to continue.",
            ]);
        }

        $this->cart->clear();

        return $order;
    }

    /**
     * Counts deal units as sold, refusing to go past a deal's quantity limit.
     *
     * @param  Collection<int, CartLine>  $lines
     *
     * @throws ValidationException
     */
    private function reserveFlashSaleQuantities(Collection $lines): void
    {
        foreach ($lines as $line) {
            if ($line->flashSaleItem === null) {
                continue;
            }

            $reserved = FlashSaleProduct::query()
                ->whereKey($line->flashSaleItem->id)
                ->where(fn (Builder $query) => $query->whereNull('quantity_limit')->orWhereRaw('sold_quantity + ? <= quantity_limit', [$line->quantity]))
                ->increment('sold_quantity', $line->quantity);

            if ($reserved !== 1) {
                throw ValidationException::withMessages([
                    'cart' => "The flash sale deal on {$line->product->name} has just sold out. Please review your order.",
                ]);
            }
        }
    }
}
