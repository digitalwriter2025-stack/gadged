<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InventoryTransactionType;
use App\Enums\OrderStatus;
use App\Models\FlashSaleProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * The only code path allowed to move an order through its fulfilment lifecycle.
 *
 * Each move is checked against OrderStatus::transitions(), stamps the matching
 * timestamp column, appends a timeline entry and, for cancellations and returns,
 * puts the reserved stock back on the shelf.
 */
class OrderStatusService
{
    public function __construct(private readonly InventoryService $inventory) {}

    /**
     * @throws InvalidArgumentException when the move is not allowed from the current status
     */
    public function transition(Order $order, OrderStatus $status, ?string $note = null, ?User $performedBy = null): Order
    {
        DB::transaction(function () use ($order, $status, $note, $performedBy): void {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $from = $locked->status;

            if (! $from->canTransitionTo($status)) {
                throw new InvalidArgumentException("An order cannot move from {$from->getLabel()} to {$status->getLabel()}.");
            }

            $locked->forceFill(['status' => $status]);

            if ($column = $status->timestampColumn()) {
                $locked->forceFill([$column => now()]);
            }

            $locked->save();

            $locked->statusHistories()->create([
                'type' => OrderStatusHistory::TYPE_STATUS,
                'from_status' => $from->value,
                'to_status' => $status->value,
                'note' => filled($note) ? $note : null,
                'user_id' => $performedBy?->id,
            ]);

            if ($status->restoresStock()) {
                $this->restoreStock($locked, $status, $performedBy);
            }

            $order->setRawAttributes($locked->getAttributes(), sync: true);
        });

        return $order;
    }

    private function restoreStock(Order $order, OrderStatus $status, ?User $performedBy): void
    {
        $items = $order->items()->get(['product_variant_id', 'flash_sale_product_id', 'quantity']);

        $quantities = $items
            ->whereNotNull('product_variant_id')
            ->groupBy('product_variant_id')
            ->map(fn ($lines): int => (int) $lines->sum('quantity'))
            ->all();

        $this->inventory->restoreForOrder(
            $quantities,
            $order,
            $status === OrderStatus::Cancelled ? InventoryTransactionType::OrderCancellation : InventoryTransactionType::Return,
            $performedBy,
        );

        if ($status === OrderStatus::Cancelled) {
            $items->whereNotNull('flash_sale_product_id')->each(function (OrderItem $item): void {
                FlashSaleProduct::query()
                    ->whereKey($item->flash_sale_product_id)
                    ->where('sold_quantity', '>=', $item->quantity)
                    ->decrement('sold_quantity', $item->quantity);
            });
        }
    }
}
