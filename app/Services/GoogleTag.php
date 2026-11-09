<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\CartLine;
use App\Support\Money;
use Illuminate\Container\Attributes\Scoped;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Google Tag Manager, GA4 and Google Ads, configured under the analytics settings group.
 *
 * Shop events use the GA4 ecommerce schema. They wait in the session until the
 * next storefront page renders (events from a redirecting POST are not lost),
 * then are pushed once: to the dataLayer for Tag Manager and, when GA4 or Ads
 * are set up directly, through gtag() with an explicit send_to so a Google tag
 * loaded by Tag Manager does not receive them a second time. Every event has an
 * id the browser remembers, so a page shown again from the history cache does
 * not repeat it.
 *
 * @phpstan-type Item array{item_id: string, item_name: string, item_brand?: string, item_category?: string, item_variant?: string, price: float, quantity: int}
 * @phpstan-type BrowserEvent array{name: string, id: string, params: array<string, mixed>, user_data?: array<string, string>}
 */
#[Scoped]
class GoogleTag
{
    public const GTM_PATTERN = '/^GTM-[A-Z0-9]{4,12}$/';

    public const MEASUREMENT_PATTERN = '/^G-[A-Z0-9]{4,15}$/';

    public const ADS_PATTERN = '/^AW-\d{6,15}$/';

    public const ADS_LABEL_PATTERN = '/^[A-Za-z0-9_-]{4,64}$/';

    /**
     * Country code added to local phone numbers for enhanced conversions.
     */
    public const COUNTRY_CALLING_CODE = '880';

    private const SESSION_EVENTS = 'google_tag.events';

    private const SESSION_CHECKOUT = 'google_tag.checkout';

    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    public function gtmId(): ?string
    {
        return $this->tagId('gtm_id', self::GTM_PATTERN);
    }

    public function measurementId(): ?string
    {
        return $this->tagId('ga4_measurement_id', self::MEASUREMENT_PATTERN);
    }

    public function adsId(): ?string
    {
        return $this->tagId('google_ads_id', self::ADS_PATTERN);
    }

    /**
     * The send_to of the Google Ads purchase conversion, "AW-123456789/AbC-D_efG".
     */
    public function adsConversion(): ?string
    {
        $label = trim((string) $this->settings->get('analytics.google_ads_conversion_label'));

        if ($this->adsId() === null || preg_match(self::ADS_LABEL_PATTERN, $label) !== 1) {
            return null;
        }

        return $this->adsId().'/'.$label;
    }

    /**
     * Whether any Google tag is printed; callers check this before loading data only tracking needs.
     */
    public function enabled(): bool
    {
        return $this->gtmId() !== null || $this->measurementId() !== null || $this->adsId() !== null;
    }

    /**
     * Tag IDs are saved upper-case; a lower-case or padded paste still works.
     */
    public static function normalizeId(?string $value): ?string
    {
        $value = Str::upper(trim((string) $value));

        return $value === '' ? null : $value;
    }

    public function viewItem(Product $product, string $price, ?string $category = null, ?string $brand = null): void
    {
        $item = $this->item((string) $product->id, $product->name, (float) $price, 1, $brand, $category);

        $this->track('view_item', [
            'currency' => $this->currency(),
            'value' => (float) $price,
            'items' => [$item],
        ]);
    }

    public function addToCart(CartLine $line, int $quantity): void
    {
        if (! $this->enabled()) {
            return;
        }

        $this->loadCatalogue(collect([$line]));

        $this->track('add_to_cart', [
            'currency' => $this->currency(),
            'value' => (float) Money::mul($line->unitPrice, $quantity),
            'items' => [$this->cartItem($line, $quantity)],
        ]);
    }

    /**
     * Tracked once per cart: reloading the checkout page, or coming back to it
     * after a validation error, does not count again until the cart changes.
     *
     * @param  Collection<int, CartLine>  $lines
     */
    public function beginCheckout(Collection $lines): void
    {
        if ($lines->isEmpty() || ! $this->enabled() || ! $this->request()->hasSession()) {
            return;
        }

        $cart = $lines->map(fn (CartLine $line): string => "{$line->variant->id}:{$line->quantity}")->implode(',');

        if ($this->request()->session()->get(self::SESSION_CHECKOUT) === $cart) {
            return;
        }

        $this->request()->session()->put(self::SESSION_CHECKOUT, $cart);
        $this->loadCatalogue($lines);

        $this->track('begin_checkout', [
            'currency' => $this->currency(),
            'value' => (float) Money::add(...$lines->map(fn (CartLine $line): string => $line->lineTotal())->all()),
            'items' => $lines->map(fn (CartLine $line): array => $this->cartItem($line, $line->quantity))->values()->all(),
        ]);
    }

    /**
     * The order number is the transaction id, so GA4 and Google Ads count the order once.
     */
    public function purchase(Order $order): void
    {
        if (! $this->enabled()) {
            return;
        }

        $order->loadMissing(['items.product.brand', 'items.product.category']);

        $this->track('purchase', array_filter([
            'transaction_id' => $order->order_number,
            'currency' => $order->currency ?: $this->currency(),
            'value' => (float) $order->grand_total,
            'shipping' => (float) $order->shipping_total,
            'tax' => (float) $order->tax_total,
            'coupon' => $order->coupon_code,
            'items' => $order->items->map(fn (OrderItem $item): array => $this->item(
                (string) ($item->product_id ?? $item->sku),
                $item->product_name,
                (float) $item->unit_price,
                $item->quantity,
                $item->product?->brand?->name,
                $item->product?->category?->name,
                $item->variant_name,
            ))->values()->all(),
        ], fn (mixed $value): bool => $value !== null && $value !== ''), $this->userData($order), 'purchase-'.$order->order_number);
    }

    public function search(string $query): void
    {
        $this->track('search', ['search_term' => Str::limit($query, 100, '')]);
    }

    /**
     * Records an event for the next storefront page.
     *
     * @param  array<string, mixed>  $params
     * @param  array<string, string>  $userData
     */
    public function track(string $eventName, array $params = [], array $userData = [], ?string $eventId = null): void
    {
        if (! $this->enabled() || ! $this->request()->hasSession()) {
            return;
        }

        $this->request()->session()->push(self::SESSION_EVENTS, array_filter([
            'name' => $eventName,
            'id' => $eventId ?? (string) Str::uuid(),
            'params' => $params,
            'user_data' => $userData,
        ], fn (mixed $value): bool => $value !== []));
    }

    /**
     * Tag data for the storefront layout, with every event still waiting for the browser.
     * "browser" configures the inline script that sends the events.
     *
     * @return array{enabled: bool, gtm_id: ?string, measurement_id: ?string, ads_id: ?string, browser: array{gtm: bool, ga4: ?string, adsConversion: ?string, currency: string}, events: list<BrowserEvent>}
     */
    public function page(): array
    {
        $enabled = $this->enabled();

        return [
            'enabled' => $enabled,
            'gtm_id' => $this->gtmId(),
            'measurement_id' => $this->measurementId(),
            'ads_id' => $this->adsId(),
            'browser' => [
                'gtm' => $this->gtmId() !== null,
                'ga4' => $this->measurementId(),
                'adsConversion' => $this->adsConversion(),
                'currency' => $this->currency(),
            ],
            'events' => $enabled && $this->request()->hasSession()
                ? array_values((array) $this->request()->session()->pull(self::SESSION_EVENTS, []))
                : [],
        ];
    }

    /**
     * A saved tag ID, or the matching one from analytics.google_tag_id, the single
     * field used before GA4, Ads and Tag Manager had their own.
     */
    private function tagId(string $key, string $pattern): ?string
    {
        foreach ([$this->settings->get("analytics.{$key}"), $this->settings->get('analytics.google_tag_id')] as $value) {
            $id = self::normalizeId(is_string($value) ? $value : null);

            if ($id !== null && preg_match($pattern, $id) === 1) {
                return $id;
            }
        }

        return null;
    }

    /**
     * @return Item
     */
    private function cartItem(CartLine $line, int $quantity): array
    {
        return $this->item(
            (string) $line->product->id,
            $line->product->name,
            (float) $line->unitPrice,
            $quantity,
            $line->product->brand?->name,
            $line->product->category?->name,
            $line->variant->is_default ? null : $line->variant->name,
        );
    }

    /**
     * @return Item
     */
    private function item(string $id, string $name, float $price, int $quantity, ?string $brand = null, ?string $category = null, ?string $variant = null): array
    {
        return array_filter([
            'item_id' => $id,
            'item_name' => $name,
            'item_brand' => $brand,
            'item_category' => $category,
            'item_variant' => $variant,
            'price' => $price,
            'quantity' => $quantity,
        ], fn (mixed $value): bool => $value !== null && $value !== '');
    }

    /**
     * Brand and category names for the items, in one query each.
     *
     * @param  Collection<int, CartLine>  $lines
     */
    private function loadCatalogue(Collection $lines): void
    {
        EloquentCollection::make($lines->map(fn (CartLine $line): Product => $line->product)->unique('id')->values()->all())
            ->loadMissing(['brand:id,name', 'category:id,name']);
    }

    /**
     * Hashed email and phone for Google Ads enhanced conversions, normalised the way Google asks.
     *
     * @return array<string, string>
     */
    private function userData(Order $order): array
    {
        $email = Str::lower(trim((string) $order->customer_email));

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $email = null;
        } elseif (preg_match('/^(.+)@(gmail|googlemail)\.com$/', $email, $matches) === 1) {
            $email = str_replace('.', '', $matches[1]).'@'.$matches[2].'.com';
        }

        $phone = preg_replace('/\D/', '', (string) $order->customer_phone);

        if (strlen($phone) === 11 && str_starts_with($phone, '01')) {
            $phone = self::COUNTRY_CALLING_CODE.substr($phone, 1);
        }

        return array_map(fn (string $value): string => hash('sha256', $value), array_filter([
            'sha256_email_address' => $email,
            'sha256_phone_number' => strlen($phone) >= 8 ? '+'.$phone : null,
        ]));
    }

    /**
     * The current request, looked up on every call rather than kept from construction.
     */
    private function request(): Request
    {
        return request();
    }

    private function currency(): string
    {
        return (string) config('shop.currency.code');
    }
}
