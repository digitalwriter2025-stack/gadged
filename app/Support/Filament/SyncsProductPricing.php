<?php

declare(strict_types=1);

namespace App\Support\Filament;

use App\Enums\ProductType;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use App\Support\Money;

/**
 * Bridges the product editor's price and stock fields to the default variant, which owns
 * the sellable price and stock. The product's own price columns are a read model.
 */
trait SyncsProductPricing
{
    /**
     * @return array{selling_price: ?string, compare_at_price: ?string, stock_quantity: int}
     */
    protected function pricingFormData(Product $product): array
    {
        if ($product->type === ProductType::Variable) {
            return [
                'selling_price' => $product->min_price,
                'compare_at_price' => null,
                'stock_quantity' => $product->total_stock,
            ];
        }

        $variant = $product->defaultVariant;
        $isDiscounted = $variant?->sale_price !== null;

        return [
            'selling_price' => $isDiscounted ? $variant->sale_price : $variant?->price,
            'compare_at_price' => $isDiscounted ? $variant->price : null,
            'stock_quantity' => $variant->stock_quantity ?? 0,
        ];
    }

    protected function syncDefaultVariant(): void
    {
        /** @var Product $product */
        $product = $this->record;

        if ($product->type === ProductType::Variable) {
            return;
        }

        $sellingPrice = Money::of($this->data['selling_price']);
        $compareAtPrice = filled($this->data['compare_at_price'] ?? null) ? Money::of($this->data['compare_at_price']) : null;
        $isDiscounted = $compareAtPrice !== null && Money::compare($compareAtPrice, $sellingPrice) > 0;

        $variant = $product->defaultVariant ?? $product->variants()->make(['is_default' => true]);

        $variant->fill([
            'sku' => $product->sku,
            'price' => $isDiscounted ? $compareAtPrice : $sellingPrice,
            'sale_price' => $isDiscounted ? $sellingPrice : null,
        ])->save();

        $performedBy = auth()->user();

        app(InventoryService::class)->setStock(
            $variant,
            (int) $this->data['stock_quantity'],
            'Updated from the product editor',
            $performedBy instanceof User ? $performedBy : null,
        );
    }
}
