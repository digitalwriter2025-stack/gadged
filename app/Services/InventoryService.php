<?php

namespace App\Services;

use App\Enums\InventoryTransactionType;
use App\Exceptions\InsufficientStockException;
use App\Models\InventoryTransaction;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

/**
 * The only code path allowed to change product_variants.stock_quantity.
 *
 * Every change runs in a transaction, locks the variant row(s) with
 * SELECT ... FOR UPDATE, refuses to go below zero and appends an
 * inventory_transactions row with before/after quantities. Multi-line
 * operations lock rows in ascending id order so concurrent checkouts
 * cannot deadlock on each other.
 */
class InventoryService
{
    public function __construct(private readonly ProductAggregateService $aggregates) {}

    /**
     * Record a single stock movement.
     *
     * @param  int  $quantity  positive amount for directional types; signed change for Adjustment
     *
     * @throws InsufficientStockException
     */
    public function adjust(
        ProductVariant $variant,
        InventoryTransactionType $type,
        int $quantity,
        ?string $reason = null,
        ?Model $reference = null,
        ?User $performedBy = null,
    ): InventoryTransaction {
        $change = $this->signedChange($type, $quantity);

        $transaction = DB::transaction(function () use ($variant, $type, $change, $reason, $reference, $performedBy): InventoryTransaction {
            $locked = $this->lockVariants([$variant->id])->first()
                ?? throw new InvalidArgumentException("Variant [{$variant->id}] does not exist.");

            $transaction = $this->apply($locked, $type, $change, $reason, $reference, $performedBy);
            $this->aggregates->refresh($locked->product_id);

            return $transaction;
        });

        $variant->forceFill(['stock_quantity' => $transaction->quantity_after])->syncOriginalAttribute('stock_quantity');

        return $transaction;
    }

    /**
     * Set an absolute stock level (stock count). Returns null when nothing changed.
     */
    public function setStock(ProductVariant $variant, int $newQuantity, string $reason, ?User $performedBy = null): ?InventoryTransaction
    {
        if ($newQuantity < 0) {
            throw new InvalidArgumentException('Stock cannot be negative.');
        }

        $transaction = DB::transaction(function () use ($variant, $newQuantity, $reason, $performedBy): ?InventoryTransaction {
            $locked = $this->lockVariants([$variant->id])->firstOrFail();
            $change = $newQuantity - $locked->stock_quantity;

            if ($change === 0) {
                return null;
            }

            $transaction = $this->apply($locked, InventoryTransactionType::Adjustment, $change, $reason, null, $performedBy);
            $this->aggregates->refresh($locked->product_id);

            return $transaction;
        });

        if ($transaction !== null) {
            $variant->forceFill(['stock_quantity' => $transaction->quantity_after])->syncOriginalAttribute('stock_quantity');
        }

        return $transaction;
    }

    /**
     * Take stock for an order. All lines succeed or none do.
     * Variants of products with manage_stock = false are skipped.
     *
     * @param  array<int, int>  $quantities  variant id => quantity
     * @return list<InventoryTransaction>
     *
     * @throws InsufficientStockException
     */
    public function deductForOrder(array $quantities, Model $order, ?User $performedBy = null): array
    {
        return $this->applyMany($quantities, InventoryTransactionType::Order, $order, $performedBy);
    }

    /**
     * Put stock back for a cancelled or returned order.
     *
     * @param  array<int, int>  $quantities  variant id => quantity
     * @return list<InventoryTransaction>
     */
    public function restoreForOrder(
        array $quantities,
        Model $order,
        InventoryTransactionType $type = InventoryTransactionType::OrderCancellation,
        ?User $performedBy = null,
    ): array {
        if ($type->direction() !== 1) {
            throw new InvalidArgumentException("[{$type->value}] does not add stock.");
        }

        return $this->applyMany($quantities, $type, $order, $performedBy);
    }

    /**
     * @param  array<int, int>  $quantities
     * @return list<InventoryTransaction>
     */
    private function applyMany(array $quantities, InventoryTransactionType $type, Model $reference, ?User $performedBy): array
    {
        $quantities = array_filter($quantities, fn (int $qty): bool => $qty > 0);

        if ($quantities === []) {
            return [];
        }

        return DB::transaction(function () use ($quantities, $type, $reference, $performedBy): array {
            $variants = $this->lockVariants(array_keys($quantities))->keyBy('id');

            if ($missing = array_diff(array_keys($quantities), $variants->keys()->all())) {
                throw new InvalidArgumentException('Unknown variant ids: '.implode(', ', $missing));
            }

            $transactions = [];
            $productIds = [];

            foreach ($variants as $variant) {
                if (! $variant->getAttribute('manage_stock')) {
                    continue;
                }

                $change = $this->signedChange($type, $quantities[$variant->id]);
                $transactions[] = $this->apply($variant, $type, $change, null, $reference, $performedBy);
                $productIds[$variant->product_id] = true;
            }

            foreach (array_keys($productIds) as $productId) {
                $this->aggregates->refresh($productId);
            }

            return $transactions;
        });
    }

    /**
     * @param  list<int>  $ids
     * @return Collection<int, ProductVariant>
     */
    private function lockVariants(array $ids)
    {
        sort($ids);

        return ProductVariant::withTrashed()
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->whereIn('product_variants.id', $ids)
            ->orderBy('product_variants.id')
            ->lockForUpdate()
            ->get([
                'product_variants.id',
                'product_variants.product_id',
                'product_variants.sku',
                'product_variants.stock_quantity',
                'products.manage_stock',
            ]);
    }

    private function apply(
        ProductVariant $variant,
        InventoryTransactionType $type,
        int $change,
        ?string $reason,
        ?Model $reference,
        ?User $performedBy,
    ): InventoryTransaction {
        $before = (int) $variant->stock_quantity;
        $after = $before + $change;

        if ($after < 0) {
            throw new InsufficientStockException($variant->id, $variant->sku, abs($change), $before);
        }

        // The row is locked, but the compare-and-set guards against any writer bypassing this service.
        $updated = ProductVariant::withTrashed()
            ->whereKey($variant->id)
            ->where('stock_quantity', $before)
            ->update(['stock_quantity' => $after]);

        if ($updated !== 1) {
            throw new LogicException("Stock for variant [{$variant->id}] changed concurrently.");
        }

        $variant->stock_quantity = $after;

        return InventoryTransaction::query()->create([
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'type' => $type,
            'quantity' => $change,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'reason' => $reason,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'performed_by' => $performedBy?->id,
        ]);
    }

    private function signedChange(InventoryTransactionType $type, int $quantity): int
    {
        if ($type->direction() === 0) {
            if ($quantity === 0) {
                throw new InvalidArgumentException('An adjustment must change the stock.');
            }

            return $quantity;
        }

        if ($quantity <= 0) {
            throw new InvalidArgumentException("Quantity for [{$type->value}] must be positive.");
        }

        return $type->direction() * $quantity;
    }
}
