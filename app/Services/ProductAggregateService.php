<?php

namespace App\Services;

use App\Enums\ReviewStatus;
use App\Enums\StockStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Support\Money;

/**
 * Maintains the denormalized read-model columns on products (price range,
 * discount, total stock, stock status, rating) from their source rows.
 * Uses query-builder updates so no model events or search re-indexing fire.
 */
class ProductAggregateService
{
    public function refresh(Product|int $product): void
    {
        $product = $product instanceof Product
            ? $product
            : Product::withTrashed()->select(['id', 'manage_stock', 'stock_status'])->find($product);

        if ($product === null) {
            return;
        }

        $variants = ProductVariant::query()
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->get(['id', 'price', 'sale_price', 'sale_starts_at', 'sale_ends_at', 'stock_quantity']);

        $values = $variants->isEmpty()
            ? ['min_price' => '0.00', 'max_price' => '0.00', 'regular_price' => '0.00', 'discount_percent' => 0, 'total_stock' => 0]
            : $this->priceAndStock($variants->all());

        $values['stock_status'] = $this->stockStatus($product, $values['total_stock'])->value;

        Product::withTrashed()->whereKey($product->id)->update($values);

        $product->forceFill($values)->syncOriginal();
    }

    public function refreshRating(Product|int $product): void
    {
        $productId = $product instanceof Product ? $product->id : $product;

        $stats = Review::query()
            ->where('product_id', $productId)
            ->where('status', ReviewStatus::Approved)
            ->selectRaw('COUNT(*) AS aggregate_count, COALESCE(AVG(rating), 0) AS aggregate_avg')
            ->toBase()
            ->first();

        Product::withTrashed()->whereKey($productId)->update([
            'rating_count' => (int) $stats->aggregate_count,
            'rating_avg' => round((float) $stats->aggregate_avg, 2),
        ]);
    }

    /**
     * @param  list<ProductVariant>  $variants
     * @return array{min_price: string, max_price: string, regular_price: string, discount_percent: int, total_stock: int}
     */
    private function priceAndStock(array $variants): array
    {
        $cheapest = null;
        $min = null;
        $max = null;
        $discount = 0;
        $stock = 0;

        foreach ($variants as $variant) {
            $current = $variant->currentPrice();
            $stock += $variant->stock_quantity;
            $discount = max($discount, Money::percentage(Money::sub($variant->price, $current), $variant->price));

            if ($min === null || Money::compare($current, $min) < 0) {
                $min = $current;
                $cheapest = $variant;
            }

            if ($max === null || Money::compare($current, $max) > 0) {
                $max = $current;
            }
        }

        return [
            'min_price' => $min,
            'max_price' => $max,
            'regular_price' => Money::of($cheapest->price),
            'discount_percent' => min(100, $discount),
            'total_stock' => $stock,
        ];
    }

    /**
     * Only flips between in-stock and out-of-stock; pre-order and upcoming are
     * editorial states that staff set explicitly.
     */
    private function stockStatus(Product $product, int $totalStock): StockStatus
    {
        $status = $product->stock_status;

        if (! $product->manage_stock) {
            return $status;
        }

        return match (true) {
            $status === StockStatus::InStock && $totalStock <= 0 => StockStatus::OutOfStock,
            $status === StockStatus::OutOfStock && $totalStock > 0 => StockStatus::InStock,
            default => $status,
        };
    }
}
