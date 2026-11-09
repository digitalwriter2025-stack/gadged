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
     * Active delivery methods the customer can pick at checkout, in display order.
     *
     * @return Collection<int, ShippingMethod>
     */
    public function shippingMethods(): Collection
    {
        return ShippingMethod::query()->active()->get();
    }

    /**
     * Minimum cart subtotal from orders.min_order_amount.
     */
    public function minimumOrderAmount(): string
    {
        return Money::of($this->settings->get('orders.min_order_amount', '0'));
    }

    /**
     * @param  array{customer_name: string, customer_phone: string, customer_email: ?string, district_id: int, shipping_method_id: ?int, shipping_area: ?string, shipping_address: string, customer_note: ?string, payment_method: string}  $details
     *
     * @throws ValidationException when the cart is empty, below the minimum or out of stock
     */
    public function placeOrder(array $details, ?User $customer = null, ?string $ipAddress = null, ?string $userAgent = null): Order
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            throw ValidationException::withMessages(['cart' => __('Your cart is empty.')]);
        }

        $subtotal = $this->cart->subtotal($lines);
        $minimum = $this->minimumOrderAmount();

        if (Money::compare($subtotal, $minimum) < 0) {
            throw ValidationException::withMessages(['cart' => __('The minimum order amount is :amount.', ['amount' => Money::format($minimum)])]);
        }

        $order = $this->createOrder($lines, $details, OrderSource::Web, '0.00', $customer, null, $ipAddress, $userAgent);

        $this->cart->clear();

        return $order;
    }

    /**
     * Order taken by staff over the phone or Facebook: the same catalog prices, stock
     * and flash sale rules as the website, plus an optional flat discount.
     *
     * @param  array{customer_name: string, customer_phone: string, customer_email: ?string, district_id: ?int, shipping_method_id: ?int, shipping_area: ?string, shipping_address: string, customer_note: ?string, payment_method: string}  $details
     * @param  array<int, int>  $quantities  variant id => quantity
     *
     * @throws ValidationException when no product can be ordered, the discount is too large or stock ran out
     */
    public function placeManualOrder(array $details, array $quantities, string $manualDiscount, User $staff): Order
    {
        $lines = $this->cart->linesFor($quantities);

        if ($lines->isEmpty()) {
            throw ValidationException::withMessages(['items' => 'Add at least one product.']);
        }

        if ($lines->count() !== count($quantities)) {
            throw ValidationException::withMessages(['items' => 'A product is no longer available. Remove it from the list and try again.']);
        }

        $manualDiscount = Money::of($manualDiscount);

        if (Money::compare($manualDiscount, $this->cart->subtotal($lines)) > 0) {
            throw ValidationException::withMessages(['extraDiscount' => 'The discount cannot be more than the product total.']);
        }

        return $this->createOrder($lines, $details, OrderSource::Admin, $manualDiscount, null, $staff);
    }

    /**
     * Saves the order, its items and the opening status entry, taking stock and
     * flash sale quantities in the same transaction.
     *
     * @param  Collection<int, CartLine>  $lines
     * @param  array{customer_name: string, customer_phone: string, customer_email: ?string, district_id: ?int, shipping_method_id: ?int, shipping_area: ?string, shipping_address: string, customer_note: ?string, payment_method: string}  $details
     *
     * @throws ValidationException when stock or a flash sale deal ran out
     */
    private function createOrder(
        Collection $lines,
        array $details,
        OrderSource $source,
        string $manualDiscount,
        ?User $customer,
        ?User $staff = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): Order {
        $subtotal = $this->cart->subtotal($lines);
        $district = $details['district_id'] === null
            ? null
            : District::query()->with('shippingZone')->findOrFail($details['district_id']);
        $method = $details['shipping_method_id'] === null
            ? null
            : ShippingMethod::query()->active()->findOrFail($details['shipping_method_id']);
        $shippingTotal = $method?->chargeFor($subtotal) ?? '0.00';
        $actor = $staff ?? $customer;

        try {
            return DB::transaction(function () use ($details, $source, $manualDiscount, $customer, $staff, $actor, $ipAddress, $userAgent, $lines, $subtotal, $district, $method, $shippingTotal): Order {
                $order = Order::query()->create([
                    'order_number' => $this->orderNumbers->next(),
                    'user_id' => $customer?->id,
                    'payment_method' => $details['payment_method'],
                    'source' => $source,
                    'customer_name' => $details['customer_name'],
                    'customer_phone' => $details['customer_phone'],
                    'customer_email' => $details['customer_email'] ?? null,
                    'shipping_address' => $details['shipping_address'],
                    'shipping_area' => $details['shipping_area'] ?? null,
                    'district_id' => $district?->id,
                    'shipping_district' => $district?->name,
                    'shipping_zone_id' => $district?->shipping_zone_id,
                    'shipping_zone_name' => $district?->shippingZone?->name,
                    'shipping_method_id' => $method?->id,
                    'shipping_method_name' => $method?->name,
                    'customer_note' => $details['customer_note'] ?? null,
                    'subtotal' => $subtotal,
                    'manual_discount' => $manualDiscount,
                    'discount_total' => $manualDiscount,
                    'shipping_total' => $shippingTotal,
                    'grand_total' => Money::add(Money::sub($subtotal, $manualDiscount), $shippingTotal),
                    'currency' => config('shop.currency.code'),
                    'created_by' => $staff?->id,
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
                    'unit_cost' => $line->variant->cost_price,
                    'quantity' => $line->quantity,
                    'line_total' => $line->lineTotal(),
                ])->all());

                $this->reserveFlashSaleQuantities($lines);

                $this->inventory->deductForOrder(
                    $lines->mapWithKeys(fn (CartLine $line): array => [$line->variant->id => $line->quantity])->all(),
                    $order,
                    $actor,
                );

                $order->statusHistories()->create([
                    'type' => 'status',
                    'to_status' => OrderStatus::Pending->value,
                    'note' => $source === OrderSource::Admin ? 'Manual order created from the admin panel.' : 'Order placed on the website.',
                    'user_id' => $actor?->id,
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
