<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Container\Attributes\Scoped;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Steadfast Courier (packzy) merchant API, configured under the steadfast settings group.
 *
 * The API key and secret key are saved encrypted. The last successful balance check is
 * stored too, so the admin page can show the balance and connection state without
 * calling the API on every page load.
 */
#[Scoped]
class SteadfastCourier
{
    public const DEFAULT_BASE_URL = 'https://portal.packzy.com/api/v1';

    public const COURIER_NAME = 'Steadfast';

    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    /**
     * Whether orders can be sent to Steadfast: switched on and both keys saved.
     */
    public function enabled(): bool
    {
        return (bool) $this->settings->get('steadfast.enabled', false) && $this->hasCredentials();
    }

    public function baseUrl(): string
    {
        $url = trim((string) $this->settings->get('steadfast.base_url'));

        return rtrim($url === '' ? self::DEFAULT_BASE_URL : $url, '/');
    }

    public function apiKey(): ?string
    {
        return $this->decrypt($this->settings->get('steadfast.api_key'));
    }

    public function secretKey(): ?string
    {
        return $this->decrypt($this->settings->get('steadfast.secret_key'));
    }

    public function hasCredentials(): bool
    {
        return $this->apiKey() !== null && $this->secretKey() !== null;
    }

    public static function encryptKey(string $key): string
    {
        return Crypt::encryptString(trim($key));
    }

    /**
     * Asks Steadfast for the current balance and remembers it with the time of the check.
     *
     * @throws ConnectionException when Steadfast cannot be reached
     * @throws RequestException when Steadfast answers with an HTTP error (usually wrong keys)
     * @throws RuntimeException when no keys are saved or the answer has no balance
     */
    public function checkBalance(): float
    {
        if (! $this->hasCredentials()) {
            throw new RuntimeException('API key এবং secret key দুটোই দিয়ে সেভ করুন।');
        }

        try {
            $response = $this->client()->get('/get_balance')->throw();
        } catch (ConnectionException|RequestException $exception) {
            $this->forgetBalance();

            throw $exception;
        }

        $balance = $response->json('current_balance');

        if (! is_numeric($balance)) {
            $this->forgetBalance();

            throw new RuntimeException($response->json('message') ?? 'Steadfast did not return a balance. Check the API key and secret key.');
        }

        $this->settings->setMany('steadfast', [
            'balance' => (float) $balance,
            'balance_checked_at' => now()->toIso8601String(),
        ]);
        $this->settings->flush();

        return (float) $balance;
    }

    /**
     * Whether confirming an order books it with Steadfast straight away. On unless the admin turns it off.
     */
    public function sendsConfirmedOrders(): bool
    {
        return (bool) $this->settings->get('steadfast.auto_send', true);
    }

    /**
     * Books an order staff have just confirmed, unless Steadfast or auto-send is off or
     * the order already has a tracking code.
     *
     * @return string|null the tracking code, or null when the order was not sent
     *
     * @throws ConnectionException when Steadfast cannot be reached
     * @throws RuntimeException when Steadfast refuses the order
     */
    public function sendConfirmedOrder(Order $order): ?string
    {
        if (! $this->enabled() || ! $this->sendsConfirmedOrders() || filled($order->tracking_number)) {
            return null;
        }

        return $this->createConsignment($order);
    }

    /**
     * Creates a Steadfast consignment for the order and saves the tracking code on it.
     *
     * The amount still due is collected on delivery, so prepaid orders go out with
     * a cash-on-delivery amount of zero.
     *
     * @throws ConnectionException when Steadfast cannot be reached
     * @throws RuntimeException when Steadfast is switched off or refuses the order
     */
    public function createConsignment(Order $order): string
    {
        if (! $this->enabled()) {
            throw new RuntimeException('Steadfast is switched off. Turn it on and save both keys under Settings → Steadfast Courier.');
        }

        $response = $this->client()->post('/create_order', $this->consignmentPayload($order));
        $trackingCode = $response->json('consignment.tracking_code');

        if (! $response->successful() || ! is_string($trackingCode) || $trackingCode === '') {
            throw new RuntimeException(self::errorMessage($response));
        }

        $order->update([
            'courier_name' => self::COURIER_NAME,
            'tracking_number' => $trackingCode,
        ]);

        return $trackingCode;
    }

    /**
     * @return array{invoice: string, recipient_name: string, recipient_phone: string, recipient_email?: string, recipient_address: string, cod_amount: float, note?: string, item_description?: string}
     */
    public function consignmentPayload(Order $order): array
    {
        $address = collect([$order->shipping_address, $order->shipping_area, $order->shipping_district])
            ->filter(fn (?string $part): bool => filled($part))
            ->implode(', ');

        $items = $order->items()
            ->get(['product_name', 'variant_name', 'quantity'])
            ->map(fn (OrderItem $item): string => collect([$item->product_name, $item->variant_name])->filter()->implode(' - ').' x'.$item->quantity)
            ->implode(', ');

        return array_filter([
            'invoice' => $order->order_number,
            'recipient_name' => Str::limit($order->customer_name, 100, ''),
            'recipient_phone' => self::localPhone($order->customer_phone),
            'recipient_email' => $order->customer_email,
            'recipient_address' => Str::limit($address, 250, ''),
            'cod_amount' => (float) self::codAmount($order),
            'note' => Str::limit((string) $order->customer_note, 250, ''),
            'item_description' => Str::limit($items, 250, ''),
        ], fn (mixed $value): bool => $value !== null && $value !== '');
    }

    /**
     * What the rider collects: the grand total less anything already paid online.
     */
    public static function codAmount(Order $order): string
    {
        return Money::floor0(Money::sub($order->grand_total, $order->paid_total));
    }

    /**
     * Whether the saved keys passed the last balance check.
     */
    public function connected(): bool
    {
        return $this->hasCredentials() && $this->lastBalance() !== null;
    }

    public function lastBalance(): ?float
    {
        $balance = $this->settings->get('steadfast.balance');

        return is_numeric($balance) ? (float) $balance : null;
    }

    public function lastCheckedAt(): ?CarbonImmutable
    {
        $checkedAt = $this->settings->get('steadfast.balance_checked_at');

        return is_string($checkedAt) && $checkedAt !== '' ? CarbonImmutable::parse($checkedAt) : null;
    }

    /**
     * Clears the remembered balance, so the page no longer reports a connection.
     */
    public function forgetBalance(): void
    {
        $this->settings->setMany('steadfast', ['balance' => null, 'balance_checked_at' => null]);
        $this->settings->flush();
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())
            ->withHeaders([
                'Api-Key' => (string) $this->apiKey(),
                'Secret-Key' => (string) $this->secretKey(),
            ])
            ->acceptJson()
            ->asJson()
            ->connectTimeout(5)
            ->timeout(15);
    }

    /**
     * Steadfast wants the 11-digit local number, e.g. 01712345678.
     */
    private static function localPhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        return str_starts_with($digits, '8801') ? substr($digits, 2) : $digits;
    }

    /**
     * The reason Steadfast gave for refusing a request, e.g. "The invoice has already been taken."
     */
    private static function errorMessage(Response $response): string
    {
        $errors = collect($response->json('errors') ?? [])->flatten()->filter(fn (mixed $error): bool => is_string($error));

        if ($errors->isNotEmpty()) {
            return $errors->implode(' ');
        }

        $message = $response->json('message');

        return is_string($message) && $message !== ''
            ? $message
            : "Steadfast answered with HTTP {$response->status()}. Check the API key and secret key.";
    }

    private function decrypt(mixed $encrypted): ?string
    {
        if (! is_string($encrypted) || $encrypted === '') {
            return null;
        }

        try {
            $value = Crypt::decryptString($encrypted);
        } catch (DecryptException) {
            return null;
        }

        return $value === '' ? null : $value;
    }
}
