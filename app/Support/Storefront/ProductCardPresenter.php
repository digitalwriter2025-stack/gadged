<?php

namespace App\Support\Storefront;

use App\Enums\StockStatus;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use App\Support\Money;

/**
 * Turns a listing product into the array rendered by <x-store.listing-card>.
 * Uses the denormalized price columns, so no variant queries are needed.
 */
class ProductCardPresenter
{
    public const FEATURE_COUNT = 4;

    public const PLACEHOLDER_IMAGE = 'images/products/placeholder.svg';

    public function __construct(
        private readonly ProductSpecifications $specifications,
    ) {}

    /**
     * Eager loads a card needs on top of Product::forCard().
     *
     * @return array<int|string, mixed>
     */
    public static function relations(): array
    {
        return [
            'specificationValues.specification.group',
            'specificationValues.option',
        ];
    }

    /**
     * @return array{id: int, name: string, url: string, image: string, price: string, regular_price: ?string, savings: ?string, features: list<string>, purchasable: bool, stock_label: string}
     */
    public function present(Product $product): array
    {
        $savings = Money::sub($product->regular_price, $product->min_price);
        $hasSavings = Money::isPositive($savings);

        return [
            'id' => $product->id,
            'name' => $product->name,
            'url' => route('store.products.show', $product->slug),
            'image' => $this->image($product, 'card'),
            'price' => Money::format($product->min_price),
            'regular_price' => $hasSavings ? Money::format($product->regular_price) : null,
            'savings' => $hasSavings ? Money::format($savings) : null,
            'features' => $product->relationLoaded('specificationValues')
                ? $this->specifications->keyFeatures($this->specifications->groups($product), self::FEATURE_COUNT)
                : [],
            // Listings only contain published products, and forCard() does not select the status columns.
            'purchasable' => in_array($product->stock_status, [StockStatus::InStock, StockStatus::PreOrder], true),
            'stock_label' => $product->stock_status->getLabel(),
        ];
    }

    /**
     * Card for a flash-sale item: the deal price, savings against the regular price
     * and the campaign end time (server clock) for the countdown.
     *
     * @return array{id: int, name: string, url: string, image: string, price: string, regular_price: ?string, savings: ?string, features: list<string>, purchasable: bool, stock_label: string, ends_at: string}
     */
    public function presentFlashDeal(Product $product, FlashSaleProduct $item, FlashSale $flashSale): array
    {
        $dealPrice = $item->priceFrom($product->regular_price);
        $savings = Money::sub($product->regular_price, $dealPrice);
        $hasSavings = Money::isPositive($savings);

        return [
            ...$this->present($product),
            'price' => Money::format($dealPrice),
            'regular_price' => $hasSavings ? Money::format($product->regular_price) : null,
            'savings' => $hasSavings ? Money::format($savings) : null,
            'ends_at' => $flashSale->ends_at->toIso8601String(),
        ];
    }

    /**
     * Main image URL, falling back to a placeholder until an image is uploaded.
     */
    public function image(Product $product, string $conversion): string
    {
        return $product->getFirstMedia('main')?->getAvailableUrl([$conversion])
            ?? asset(self::PLACEHOLDER_IMAGE);
    }
}
