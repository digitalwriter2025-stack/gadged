<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Support\CartLine;
use App\Support\Money;
use Illuminate\Container\Attributes\Scoped;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

use function Illuminate\Support\defer;

/**
 * Meta (Facebook) Pixel and Conversions API, configured under the meta_pixel settings group.
 *
 * Every event gets one event_id shared by the browser pixel and the server request,
 * so Meta counts it once. Browser events wait in the session until the next
 * storefront page renders (events from a redirecting POST are not lost); server
 * events are sent to the Graph API after the response has gone out.
 *
 * @phpstan-type Customer array{email?: ?string, phone?: ?string, name?: ?string, city?: ?string}
 * @phpstan-type BrowserEvent array{name: string, data: array<string, mixed>, id: string}
 */
#[Scoped]
class MetaPixel
{
    public const GRAPH_URL = 'https://graph.facebook.com';

    public const DEFAULT_API_VERSION = 'v21.0';

    /**
     * Ways to identify a product in content_ids; must match the catalogue feed id column.
     *
     * @var array<string, string>
     */
    public const CONTENT_ID_OPTIONS = [
        'id' => 'Product ID (default)',
        'sku' => 'Product SKU',
    ];

    /**
     * Customer country for advanced matching (ISO 3166-1 alpha-2, lowercase).
     */
    public const COUNTRY = 'bd';

    private const SESSION_EVENTS = 'meta_pixel.events';

    private const SESSION_CUSTOMER = 'meta_pixel.customer';

    private const SESSION_CLICK_ID = 'meta_pixel.fbc';

    private const SESSION_CHECKOUT = 'meta_pixel.checkout';

    /** @var list<array<string, mixed>> */
    private array $serverEvents = [];

    private bool $flushScheduled = false;

    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    public function pixelId(): ?string
    {
        // analytics.meta_pixel_id is where the ID was saved before the Meta settings section existed.
        $id = preg_replace('/\D/', '', (string) ($this->settings->get('meta_pixel.pixel_id') ?: $this->settings->get('analytics.meta_pixel_id')));

        return $id === '' ? null : $id;
    }

    /**
     * Whether any event is sent at all; callers check this before loading data only tracking needs.
     */
    public function enabled(): bool
    {
        return $this->browserEnabled() || $this->serverEnabled();
    }

    public function browserEnabled(): bool
    {
        return $this->pixelId() !== null && (bool) $this->settings->get('meta_pixel.browser_enabled', true);
    }

    public function serverEnabled(): bool
    {
        return $this->pixelId() !== null
            && $this->accessToken() !== null
            && (bool) $this->settings->get('meta_pixel.server_enabled', false);
    }

    public function hasAccessToken(): bool
    {
        return $this->accessToken() !== null;
    }

    /**
     * The Conversions API token, saved encrypted by the settings page.
     */
    public function accessToken(): ?string
    {
        $encrypted = $this->settings->get('meta_pixel.access_token');

        if (! is_string($encrypted) || $encrypted === '') {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (DecryptException) {
            return null;
        }
    }

    public static function encryptAccessToken(string $token): string
    {
        return Crypt::encryptString(trim($token));
    }

    /**
     * The value of a facebook-domain-verification meta tag; a whole pasted tag is reduced to its content.
     */
    public static function domainVerificationCode(?string $value): ?string
    {
        $value = trim((string) $value);

        if (preg_match('/content\s*=\s*["\']([^"\']*)["\']/i', $value, $matches) === 1) {
            $value = $matches[1];
        }

        $code = preg_replace('/[^A-Za-z0-9]/', '', $value);

        return $code === '' ? null : $code;
    }

    public function contentId(?Product $product, ?string $fallbackSku = null, ?int $fallbackId = null): string
    {
        return $this->settings->get('meta_pixel.content_id', 'id') === 'sku'
            ? (string) ($product->sku ?? $fallbackSku ?? $fallbackId)
            : (string) ($product->id ?? $fallbackId ?? $fallbackSku);
    }

    public function viewContent(Product $product, string $price, ?string $category = null): void
    {
        $this->track('ViewContent', array_filter([
            'content_ids' => [$this->contentId($product)],
            'content_type' => 'product',
            'content_name' => $product->name,
            'content_category' => $category,
            'value' => (float) $price,
            'currency' => $this->currency(),
        ], fn (mixed $value): bool => $value !== null));
    }

    public function addToCart(CartLine $line, int $quantity): void
    {
        $id = $this->contentId($line->product);

        $this->track('AddToCart', [
            'content_ids' => [$id],
            'content_type' => 'product',
            'content_name' => $line->product->name,
            'contents' => [['id' => $id, 'quantity' => $quantity, 'item_price' => (float) $line->unitPrice]],
            'value' => (float) Money::mul($line->unitPrice, $quantity),
            'currency' => $this->currency(),
        ]);
    }

    /**
     * Tracked once per cart: reloading the checkout page, or coming back to it
     * after a validation error, does not count again until the cart changes.
     *
     * @param  Collection<int, CartLine>  $lines
     */
    public function initiateCheckout(Collection $lines): void
    {
        if ($lines->isEmpty() || ! $this->request()->hasSession()) {
            return;
        }

        $cart = $lines->map(fn (CartLine $line): string => "{$line->variant->id}:{$line->quantity}")->implode(',');

        if ($this->request()->session()->get(self::SESSION_CHECKOUT) === $cart) {
            return;
        }

        $this->request()->session()->put(self::SESSION_CHECKOUT, $cart);

        $contents = $lines->map(fn (CartLine $line): array => [
            'id' => $this->contentId($line->product),
            'quantity' => $line->quantity,
            'item_price' => (float) $line->unitPrice,
        ])->all();

        $this->track('InitiateCheckout', [
            'content_ids' => array_values(array_unique(array_column($contents, 'id'))),
            'content_type' => 'product',
            'contents' => $contents,
            'num_items' => array_sum(array_column($contents, 'quantity')),
            'value' => (float) Money::add(...$lines->map(fn (CartLine $line): string => $line->lineTotal())->all()),
            'currency' => $this->currency(),
        ]);
    }

    /**
     * The order number is the event id, so the same order is never counted twice.
     */
    public function purchase(Order $order): void
    {
        $order->loadMissing('items.product');

        $contents = $order->items->map(fn (OrderItem $item): array => [
            'id' => $this->contentId($item->product, $item->sku, $item->product_id),
            'quantity' => $item->quantity,
            'item_price' => (float) $item->unit_price,
        ])->all();

        $this->track('Purchase', [
            'content_ids' => array_values(array_unique(array_column($contents, 'id'))),
            'content_type' => 'product',
            'contents' => $contents,
            'num_items' => array_sum(array_column($contents, 'quantity')),
            'value' => (float) $order->grand_total,
            'currency' => $order->currency ?: $this->currency(),
            'order_id' => $order->order_number,
        ], [
            'email' => $order->customer_email,
            'phone' => $order->customer_phone,
            'name' => $order->customer_name,
            'city' => $order->shipping_district,
        ], 'purchase-'.$order->order_number);
    }

    public function search(string $query): void
    {
        $this->track('Search', ['search_string' => Str::limit($query, 100, '')]);
    }

    /**
     * Records an event for the browser pixel and the Conversions API.
     *
     * @param  array<string, mixed>  $customData
     * @param  Customer  $customer
     */
    public function track(string $eventName, array $customData = [], array $customer = [], ?string $eventId = null): void
    {
        $browser = $this->browserEnabled();
        $server = $this->serverEnabled();

        if (! $browser && ! $server) {
            return;
        }

        $eventId ??= (string) Str::uuid();

        if ($customer !== [] && $this->advancedMatching() && $this->request()->hasSession()) {
            $this->request()->session()->put(self::SESSION_CUSTOMER, array_filter($customer));
        }

        if ($browser && $this->request()->hasSession()) {
            $this->request()->session()->push(self::SESSION_EVENTS, ['name' => $eventName, 'data' => $customData, 'id' => $eventId]);
        }

        if ($server) {
            $this->queueServerEvent($eventName, $eventId, $customData, $customer);
        }
    }

    /**
     * Pixel data for the storefront layout. Records the page view and hands over
     * every event still waiting for the browser.
     *
     * @return array{domain_verification: ?string, pixel_id: ?string, advanced_matching: array<string, string>, page_view_id: string, events: list<BrowserEvent>}
     */
    public function page(): array
    {
        $this->rememberClickId();

        $pageViewId = (string) Str::uuid();
        $browser = $this->browserEnabled();

        if ($this->serverEnabled() && $this->request()->isMethod('GET')) {
            $this->queueServerEvent('PageView', $pageViewId);
        }

        return [
            'domain_verification' => self::domainVerificationCode($this->settings->get('meta_pixel.domain_verification')),
            'pixel_id' => $browser ? $this->pixelId() : null,
            'advanced_matching' => $browser ? $this->hashedCustomer() : [],
            'page_view_id' => $pageViewId,
            'events' => $browser && $this->request()->hasSession()
                ? array_values((array) $this->request()->session()->pull(self::SESSION_EVENTS, []))
                : [],
        ];
    }

    /**
     * Sends one PageView to the Conversions API right away, for checking the setup
     * from the admin panel. Uses the saved test event code when there is one.
     *
     * @throws RequestException|ConnectionException|RuntimeException
     */
    public function sendTestEvent(): Response
    {
        return $this->sendToConversionsApi([[
            'event_name' => 'PageView',
            'event_time' => now()->getTimestamp(),
            'event_id' => (string) Str::uuid(),
            'event_source_url' => url('/'),
            'action_source' => 'website',
            'user_data' => $this->userData(),
        ]]);
    }

    public function testEventCode(): ?string
    {
        $code = trim((string) $this->settings->get('meta_pixel.test_event_code'));

        return $code === '' ? null : $code;
    }

    /**
     * @param  array<string, mixed>  $customData
     * @param  Customer  $customer
     */
    private function queueServerEvent(string $eventName, string $eventId, array $customData = [], array $customer = []): void
    {
        $this->serverEvents[] = array_filter([
            'event_name' => $eventName,
            'event_time' => now()->getTimestamp(),
            'event_id' => $eventId,
            'event_source_url' => $this->sourceUrl(),
            'action_source' => 'website',
            'user_data' => $this->userData($customer),
            'custom_data' => $customData,
        ], fn (mixed $value): bool => $value !== [] && $value !== null);

        if (! $this->flushScheduled) {
            $this->flushScheduled = true;

            defer(fn () => $this->flushServerEvents());
        }
    }

    /**
     * Sends the events recorded during this request in one Graph API call. Tracking
     * must never break the shop, so failures are only logged.
     */
    private function flushServerEvents(): void
    {
        $events = $this->serverEvents;
        $this->serverEvents = [];
        $this->flushScheduled = false;

        if ($events === []) {
            return;
        }

        try {
            $this->sendToConversionsApi($events);
        } catch (RequestException $exception) {
            Log::warning('Meta Conversions API rejected events.', [
                'events' => array_column($events, 'event_name'),
                'error' => $exception->response->json('error.message') ?? $exception->getMessage(),
            ]);
        } catch (ConnectionException $exception) {
            Log::warning('Meta Conversions API could not be reached.', ['error' => $exception->getMessage()]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $events
     *
     * @throws RequestException|ConnectionException|RuntimeException when the pixel ID or token is missing
     */
    private function sendToConversionsApi(array $events): Response
    {
        $pixelId = $this->pixelId();
        $token = $this->accessToken();

        if ($pixelId === null || $token === null) {
            throw new RuntimeException('Save a Meta Pixel ID and a Conversions API access token first.');
        }

        return Http::acceptJson()
            ->connectTimeout(3)
            ->timeout(5)
            ->post(self::GRAPH_URL."/{$this->apiVersion()}/{$pixelId}/events", array_filter([
                'data' => $events,
                'access_token' => $token,
                'test_event_code' => $this->testEventCode(),
            ]))
            ->throw();
    }

    /**
     * Visitor details Meta matches events with. Personal details are sent only
     * as SHA-256 hashes, and only when advanced matching is on.
     *
     * @param  Customer  $customer
     * @return array<string, string>
     */
    private function userData(array $customer = []): array
    {
        return array_filter([
            'client_ip_address' => $this->request()->ip(),
            'client_user_agent' => $this->request()->userAgent(),
            'fbp' => $this->request()->cookie('_fbp'),
            'fbc' => $this->clickId(),
            ...$this->hashedCustomer($customer),
        ], fn (mixed $value): bool => is_string($value) && $value !== '');
    }

    /**
     * @param  Customer  $customer
     * @return array<string, string>
     */
    private function hashedCustomer(array $customer = []): array
    {
        if (! $this->advancedMatching()) {
            return [];
        }

        $user = $this->request()->user();
        $remembered = $this->request()->hasSession() ? (array) $this->request()->session()->get(self::SESSION_CUSTOMER, []) : [];
        $customer = array_filter($customer) + $remembered + ($user instanceof User ? array_filter([
            'email' => $user->email,
            'name' => $user->name,
        ]) : []);

        $names = preg_split('/\s+/u', $this->normalizeText($customer['name'] ?? null, keepSpaces: true), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_map(
            fn (string $value): string => hash('sha256', $value),
            array_filter([
                'em' => $this->normalizeEmail($customer['email'] ?? null),
                'ph' => $this->normalizePhone($customer['phone'] ?? null),
                'fn' => $names[0] ?? null,
                'ln' => count($names) > 1 ? end($names) : null,
                'ct' => $this->normalizeText($customer['city'] ?? null),
                'country' => self::COUNTRY,
                'external_id' => $user instanceof User ? (string) $user->getKey() : null,
            ], fn (?string $value): bool => $value !== null && $value !== ''),
        );
    }

    private function normalizeEmail(?string $email): ?string
    {
        $email = Str::lower(trim((string) $email));

        return filter_var($email, FILTER_VALIDATE_EMAIL) === false ? null : $email;
    }

    /**
     * Digits with the country code: 01712345678 becomes 8801712345678.
     */
    private function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if (strlen($digits) === 11 && str_starts_with($digits, '01')) {
            $digits = '88'.$digits;
        }

        return strlen($digits) >= 8 ? $digits : null;
    }

    private function normalizeText(?string $value, bool $keepSpaces = false): string
    {
        $pattern = $keepSpaces ? '/[^\p{L}\s]/u' : '/[^\p{L}]/u';

        return trim((string) preg_replace($pattern, '', mb_strtolower((string) $value)));
    }

    /**
     * The fbc click id: the pixel's _fbc cookie, otherwise one built from an fbclid
     * landing parameter and kept for the rest of the visit.
     */
    private function clickId(): ?string
    {
        return $this->request()->cookie('_fbc')
            ?? ($this->request()->hasSession() ? $this->request()->session()->get(self::SESSION_CLICK_ID) : null);
    }

    private function rememberClickId(): void
    {
        $fbclid = $this->request()->query('fbclid');

        if (is_string($fbclid) && $fbclid !== '' && $this->request()->hasSession()) {
            $this->request()->session()->put(self::SESSION_CLICK_ID, 'fb.1.'.now()->getTimestampMs().'.'.Str::limit($fbclid, 500, ''));
        }
    }

    /**
     * The page the visitor was on: for a form post, the page the form was on.
     */
    private function sourceUrl(): string
    {
        return $this->request()->isMethod('GET')
            ? $this->request()->fullUrl()
            : ($this->request()->headers->get('referer') ?: $this->request()->fullUrl());
    }

    /**
     * The current request, looked up on every call rather than kept from construction.
     */
    private function request(): Request
    {
        return request();
    }

    private function advancedMatching(): bool
    {
        return (bool) $this->settings->get('meta_pixel.advanced_matching', false);
    }

    private function apiVersion(): string
    {
        $version = (string) $this->settings->get('meta_pixel.api_version', self::DEFAULT_API_VERSION);

        return preg_match('/^v\d+\.\d+$/', $version) === 1 ? $version : self::DEFAULT_API_VERSION;
    }

    private function currency(): string
    {
        return (string) config('shop.currency.code');
    }
}
