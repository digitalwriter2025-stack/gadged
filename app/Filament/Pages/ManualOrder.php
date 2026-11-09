<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\PaymentMethod;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Support\CartLine;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Staff enter orders that arrive by phone or Facebook message. Products are
 * priced from the catalog through CheckoutService, exactly like the website.
 */
class ManualOrder extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Manual Order';

    protected static ?string $title = 'Manual Order';

    protected static ?string $slug = 'manual-order';

    protected static ?int $navigationSort = 2;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected string $view = 'filament.pages.manual-order';

    public string $customerName = '';

    public string $customerPhone = '';

    public string $shippingAddress = '';

    public ?string $shippingMethodId = null;

    public string $paymentMethod = PaymentMethod::CashOnDelivery->value;

    public string $customerNote = '';

    public ?string $extraDiscount = '0';

    public string $search = '';

    /**
     * @var list<array{variant_id: int, quantity: int}>
     */
    public array $items = [];

    public static function canAccess(): bool
    {
        return Auth::user()?->can('create', Order::class) ?? false;
    }

    /**
     * The page draws its own heading block, as in the design.
     */
    public function getHeading(): string
    {
        return '';
    }

    /**
     * @return Collection<int, ShippingMethod>
     */
    #[Computed]
    public function shippingMethods(): Collection
    {
        return app(CheckoutService::class)->shippingMethods();
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function paymentMethods(): array
    {
        return collect(PaymentMethod::cases())
            ->mapWithKeys(fn (PaymentMethod $method): array => [$method->value => $method->getLabel()])
            ->all();
    }

    /**
     * Active variants of published products matching the product name, model or SKU.
     *
     * @return list<array{variant_id: int, name: string, variant: ?string, sku: ?string, price: string, stock: int, image: ?string, available: bool}>
     */
    #[Computed]
    public function searchResults(): array
    {
        $term = trim($this->search);

        if (mb_strlen($term) < 2) {
            return [];
        }

        $like = '%'.addcslashes($term, '%_\\').'%';

        return ProductVariant::query()
            ->active()
            ->whereHas('product', fn (Builder $product) => $product->published())
            ->where(fn (Builder $query) => $query
                ->where('sku', 'like', $like)
                ->orWhere('barcode', 'like', $like)
                ->orWhere('name', 'like', $like)
                ->orWhereHas('product', fn (Builder $product) => $product
                    ->where('name', 'like', $like)
                    ->orWhere('name_bn', 'like', $like)
                    ->orWhere('model', 'like', $like)
                    ->orWhere('sku', 'like', $like)))
            ->with(['media', 'product.media' => fn ($media) => $media->where('collection_name', 'main')])
            ->limit(10)
            ->get()
            ->map(fn (ProductVariant $variant): array => [
                'variant_id' => $variant->id,
                'name' => $variant->product->name,
                'variant' => $variant->product->isVariable() ? $variant->name : null,
                'sku' => $variant->sku,
                'price' => Money::format($variant->currentPrice()),
                'stock' => $variant->stock_quantity,
                'image' => $this->imageUrl($variant),
                'available' => $variant->product->isPurchasable(),
            ])
            ->all();
    }

    /**
     * Added products priced from the catalog, keyed by variant id.
     *
     * @return Collection<int, CartLine>
     */
    #[Computed]
    public function lines(): Collection
    {
        return app(CartService::class)->linesFor($this->quantities())->keyBy(fn (CartLine $line): int => $line->variant->id);
    }

    /**
     * @return array{subtotal: string, shipping: string, discount: string, total: string}
     */
    #[Computed]
    public function totals(): array
    {
        $subtotal = app(CartService::class)->subtotal($this->lines);
        $method = $this->shippingMethods->firstWhere('id', (int) $this->shippingMethodId);
        $shipping = $this->lines->isEmpty() ? '0.00' : ($method?->chargeFor($subtotal) ?? '0.00');
        $discount = Money::min(Money::of(is_numeric($this->extraDiscount) ? max(0, (float) $this->extraDiscount) : 0), $subtotal);

        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'discount' => $discount,
            'total' => Money::add(Money::sub($subtotal, $discount), $shipping),
        ];
    }

    public function imageUrl(ProductVariant $variant): ?string
    {
        $media = $variant->getFirstMedia('image') ?? $variant->product->getFirstMedia('main');

        return $media instanceof Media ? $media->getAvailableUrl(['thumb']) : null;
    }

    public function addProduct(int $variantId): void
    {
        $line = app(CartService::class)->linesFor([$variantId => 1])->first();

        if ($line === null) {
            Notification::make()->title('This product cannot be ordered right now')->danger()->send();

            return;
        }

        foreach ($this->items as $index => $item) {
            if ($item['variant_id'] === $variantId) {
                $this->changeQuantity($index, 1);
                $this->search = '';

                return;
            }
        }

        $this->items[] = ['variant_id' => $variantId, 'quantity' => 1];
        $this->search = '';
    }

    public function changeQuantity(int $index, int $step): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $this->items[$index]['quantity'] = max(1, min((int) $this->items[$index]['quantity'] + $step, app(CartService::class)->maxQuantity()));
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    /**
     * Keeps typed quantities inside 1 .. the per-line maximum.
     */
    public function updatedItems(): void
    {
        $max = app(CartService::class)->maxQuantity();

        foreach ($this->items as $index => $item) {
            $this->items[$index]['quantity'] = max(1, min((int) $item['quantity'], $max));
        }
    }

    public function placeOrder(CheckoutService $checkout): void
    {
        $this->customerPhone = (string) preg_replace('/^\+?88(?=01)/', '', (string) preg_replace('/[\s\-()]/', '', $this->customerPhone));

        $shippingMethodIds = $this->shippingMethods->modelKeys();

        $this->validate([
            'customerName' => ['required', 'string', 'max:150'],
            'customerPhone' => ['required', 'string', 'regex:/^01[3-9]\d{8}$/'],
            'shippingAddress' => ['required', 'string', 'max:500'],
            'shippingMethodId' => $shippingMethodIds === [] ? ['nullable'] : ['required', Rule::in(array_map('strval', $shippingMethodIds))],
            'paymentMethod' => ['required', Rule::enum(PaymentMethod::class)],
            'customerNote' => ['nullable', 'string', 'max:1000'],
            'extraDiscount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
        ], [
            'customerPhone.regex' => 'Enter a valid 11-digit Bangladeshi mobile number, e.g. 01712345678.',
            'items.required' => 'Add at least one product.',
            'items.min' => 'Add at least one product.',
        ], [
            'customerName' => 'name',
            'customerPhone' => 'phone number',
            'shippingAddress' => 'address',
            'shippingMethodId' => 'delivery location',
            'paymentMethod' => 'payment method',
            'customerNote' => 'note',
            'extraDiscount' => 'discount',
        ]);

        try {
            $order = $checkout->placeManualOrder([
                'customer_name' => trim($this->customerName),
                'customer_phone' => $this->customerPhone,
                'customer_email' => null,
                'district_id' => null,
                'shipping_method_id' => filled($this->shippingMethodId) ? (int) $this->shippingMethodId : null,
                'shipping_area' => null,
                'shipping_address' => trim($this->shippingAddress),
                'customer_note' => filled($this->customerNote) ? trim($this->customerNote) : null,
                'payment_method' => $this->paymentMethod,
            ], $this->quantities(), (string) ($this->extraDiscount ?: '0'), Auth::user());
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Order could not be created')
                ->body(collect($exception->errors())->flatten()->first())
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title("Order {$order->order_number} created")
            ->body('Total: '.Money::format($order->grand_total))
            ->success()
            ->actions([
                Action::make('view')
                    ->label('View order')
                    ->url(OrderResource::getUrl('view', ['record' => $order])),
            ])
            ->send();

        $this->reset(['customerName', 'customerPhone', 'shippingAddress', 'shippingMethodId', 'paymentMethod', 'customerNote', 'extraDiscount', 'search', 'items']);
    }

    /**
     * @return array<int, int> variant id => quantity
     */
    private function quantities(): array
    {
        return collect($this->items)
            ->mapWithKeys(fn (array $item): array => [(int) $item['variant_id'] => max(1, (int) $item['quantity'])])
            ->all();
    }
}
