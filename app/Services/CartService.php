<?php

namespace App\Services;

use App\Models\FlashSaleProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\CartLine;
use App\Support\Money;
use Illuminate\Contracts\Session\Session;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Keeps the shopper's cart in the session as variant id => quantity. Lines are
 * priced from the catalog on every read, and variants that can no longer be
 * bought are dropped.
 */
class CartService
{
    public const SESSION_KEY = 'cart';

    public function __construct(private readonly Session $session) {}

    /**
     * @return array<int, int> variant id => quantity
     */
    public function quantities(): array
    {
        $stored = $this->session->get(self::SESSION_KEY, []);

        return collect(is_array($stored) ? $stored : [])
            ->filter(fn (mixed $quantity, mixed $variantId): bool => is_int($variantId) && is_int($quantity) && $quantity > 0)
            ->all();
    }

    public function count(): int
    {
        return array_sum($this->quantities());
    }

    /**
     * Add a product's default variant. Returns false when the product cannot be ordered.
     */
    public function add(Product $product, int $quantity): bool
    {
        $variant = $product->isPurchasable() ? $this->defaultVariant($product) : null;

        if ($variant === null || $quantity < 1) {
            return false;
        }

        $quantities = $this->quantities();
        $quantities[$variant->id] = min(($quantities[$variant->id] ?? 0) + $quantity, $this->maxQuantity());

        $this->store($quantities);

        return true;
    }

    /**
     * Change a line's quantity; zero or less removes it.
     */
    public function update(int $variantId, int $quantity): void
    {
        $quantities = $this->quantities();

        if (! array_key_exists($variantId, $quantities)) {
            return;
        }

        if ($quantity < 1) {
            unset($quantities[$variantId]);
        } else {
            $quantities[$variantId] = min($quantity, $this->maxQuantity());
        }

        $this->store($quantities);
    }

    public function remove(int $variantId): void
    {
        $this->update($variantId, 0);
    }

    public function clear(): void
    {
        $this->session->forget(self::SESSION_KEY);
    }

    /**
     * Priced lines in the order they were added.
     *
     * @return Collection<int, CartLine>
     */
    public function lines(): Collection
    {
        $quantities = $this->quantities();
        $lines = $this->linesFor($quantities);

        if ($lines->count() !== count($quantities)) {
            $this->store($lines->mapWithKeys(fn (CartLine $line): array => [$line->variant->id => $line->quantity])->all());
        }

        return $lines;
    }

    /**
     * Prices the given variant quantities from the catalog, dropping variants that
     * can no longer be bought. Used for the session cart and for manual admin orders.
     *
     * @param  array<int, int>  $quantities  variant id => quantity
     * @return Collection<int, CartLine>
     */
    public function linesFor(array $quantities): Collection
    {
        if ($quantities === []) {
            return collect();
        }

        $variants = ProductVariant::query()
            ->active()
            ->whereKey(array_keys($quantities))
            ->with(['product.media' => fn ($query) => $query->where('collection_name', 'main')])
            ->get()
            ->keyBy('id');

        $flashSaleItems = $this->runningFlashSaleItems($variants->pluck('product_id')->unique()->all());

        return collect($quantities)
            ->map(function (int $quantity, int $variantId) use ($variants, $flashSaleItems): ?CartLine {
                $variant = $variants->get($variantId);

                return $variant?->product?->isPurchasable() ? $this->price($variant, $quantity, $flashSaleItems) : null;
            })
            ->filter()
            ->values();
    }

    /**
     * @param  Collection<int, CartLine>  $lines
     */
    public function subtotal(Collection $lines): string
    {
        return Money::add(...$lines->map(fn (CartLine $line): string => $line->lineTotal())->all());
    }

    public function maxQuantity(): int
    {
        return (int) config('shop.cart.max_quantity_per_line');
    }

    /**
     * The variant a product page sells: the default one, otherwise the cheapest active variant.
     */
    private function defaultVariant(Product $product): ?ProductVariant
    {
        $variants = $product->activeVariants()->get();

        return $variants->firstWhere('is_default', true)
            ?? $variants->sortBy(fn (ProductVariant $variant): float => (float) $variant->currentPrice())->first();
    }

    /**
     * Same rule as the product page: the lower of the variant's own price and a running
     * flash sale deal, as long as the deal still covers the requested quantity.
     *
     * @param  Collection<int, FlashSaleProduct>  $flashSaleItems
     */
    private function price(ProductVariant $variant, int $quantity, Collection $flashSaleItems): CartLine
    {
        $regularPrice = Money::of($variant->price);
        $unitPrice = $variant->currentPrice();

        $flashSaleItem = $flashSaleItems->first(fn (FlashSaleProduct $item): bool => $item->product_id === $variant->product_id
            && ($item->product_variant_id === null || $item->product_variant_id === $variant->id));

        if ($flashSaleItem !== null && $this->dealCovers($flashSaleItem, $quantity)) {
            $dealPrice = $flashSaleItem->priceFrom($regularPrice);

            if (Money::compare($dealPrice, $unitPrice) < 0) {
                return new CartLine($variant->product, $variant, $quantity, $regularPrice, $dealPrice, $flashSaleItem);
            }
        }

        return new CartLine($variant->product, $variant, $quantity, $regularPrice, $unitPrice);
    }

    private function dealCovers(FlashSaleProduct $item, int $quantity): bool
    {
        $remaining = $item->remainingQuantity();

        return ($item->per_order_limit === null || $quantity <= $item->per_order_limit)
            && ($remaining === null || $quantity <= $remaining);
    }

    /**
     * Variant-specific deals first, so they win over product-wide ones.
     *
     * @param  list<int>  $productIds
     * @return Collection<int, FlashSaleProduct>
     */
    private function runningFlashSaleItems(array $productIds): Collection
    {
        if ($productIds === []) {
            return collect();
        }

        return FlashSaleProduct::query()
            ->whereIn('product_id', $productIds)
            ->whereHas('flashSale', fn (Builder $query) => $query->running())
            ->orderByRaw('product_variant_id IS NULL')
            ->get();
    }

    /**
     * @param  array<int, int>  $quantities
     */
    private function store(array $quantities): void
    {
        $this->session->put(self::SESSION_KEY, $quantities);
    }
}
