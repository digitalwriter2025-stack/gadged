<?php

namespace App\Support\Storefront;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Support\Localization;
use App\Support\Money;
use Carbon\CarbonInterface;

/**
 * Turns an order into the progress timeline shown on the Track Order page.
 * Only delivery-safe details are exposed: no address, email or phone.
 *
 * @phpstan-type TrackingStep array{label: string, description: string, date: ?string, state: 'done'|'current'|'upcoming'}
 */
class OrderTrackingPresenter
{
    /**
     * The normal fulfilment path, in order, with the text shown for each step.
     *
     * @var array<string, string>
     */
    private const STEPS = [
        'pending' => 'We have received your order and will call you to confirm it.',
        'confirmed' => 'Your order is confirmed and reserved for you.',
        'processing' => 'We are preparing and checking your products.',
        'packed' => 'Your parcel is packed and waiting for the courier.',
        'shipped' => 'Your parcel is on the way with our delivery partner.',
        'delivered' => 'Delivered. Enjoy your new gadget!',
    ];

    /**
     * @return array{
     *     number: string,
     *     placed_at: string,
     *     status: string,
     *     is_closed: bool,
     *     closed_message: ?string,
     *     payment_method: string,
     *     payment_status: string,
     *     courier: ?string,
     *     tracking_number: ?string,
     *     district: ?string,
     *     grand_total: string,
     *     items: list<array{name: string, variant: ?string, quantity: int}>,
     *     steps: list<TrackingStep>
     * }
     */
    public function present(Order $order): array
    {
        $order->loadMissing([
            'items:id,order_id,product_name,variant_name,quantity',
            'statusHistories' => fn ($query) => $query->where('type', OrderStatusHistory::TYPE_STATUS)->oldest('id'),
        ]);

        $isClosed = ! array_key_exists($order->status->value, self::STEPS);

        return [
            'number' => $order->order_number,
            'placed_at' => $this->date($order->created_at),
            'status' => $order->status->getLabel(),
            'is_closed' => $isClosed,
            'closed_message' => $isClosed ? $this->closedMessage($order->status) : null,
            'payment_method' => $order->payment_method->getLabel(),
            'payment_status' => $order->payment_status->getLabel(),
            'courier' => $order->courier_name,
            'tracking_number' => $order->tracking_number,
            'district' => $order->shipping_district,
            'grand_total' => Money::format($order->grand_total),
            'items' => $order->items
                ->map(fn (OrderItem $item): array => [
                    'name' => $item->product_name,
                    'variant' => $item->variant_name,
                    'quantity' => (int) $item->quantity,
                ])
                ->all(),
            'steps' => $this->steps($order, $isClosed),
        ];
    }

    /**
     * @return list<TrackingStep>
     */
    private function steps(Order $order, bool $isClosed): array
    {
        $reachedAt = ['pending' => $order->created_at];

        foreach ($order->statusHistories as $history) {
            $reachedAt[$history->to_status] ??= $history->created_at;
        }

        $statuses = array_keys(self::STEPS);
        $currentIndex = $isClosed
            ? $this->lastReachedIndex($statuses, $reachedAt)
            : array_search($order->status->value, $statuses, true);

        $steps = [];

        foreach (self::STEPS as $status => $description) {
            $index = array_search($status, $statuses, true);

            $state = match (true) {
                $index < $currentIndex => 'done',
                $index === $currentIndex => $isClosed || $status === OrderStatus::Delivered->value ? 'done' : 'current',
                default => 'upcoming',
            };

            $steps[] = [
                'label' => $status === 'pending' ? __('Order Placed') : OrderStatus::from($status)->getLabel(),
                'description' => __($description),
                'date' => $state === 'upcoming' || ! isset($reachedAt[$status]) ? null : $this->date($reachedAt[$status]),
                'state' => $state,
            ];
        }

        return $steps;
    }

    /**
     * Furthest normal step a cancelled / returned order reached before it left the path.
     *
     * @param  list<string>  $statuses
     * @param  array<string, CarbonInterface>  $reachedAt
     */
    private function lastReachedIndex(array $statuses, array $reachedAt): int
    {
        $last = 0;

        foreach ($statuses as $index => $status) {
            if (isset($reachedAt[$status])) {
                $last = $index;
            }
        }

        return $last;
    }

    private function closedMessage(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::Cancelled => __('This order has been cancelled. If you did not request this, please call our hotline.'),
            OrderStatus::Returned => __('This order has been returned to us. Our team will contact you about the next steps.'),
            OrderStatus::Refunded => __('This order has been refunded. Refunds can take a few working days to reach your account.'),
            default => __('This order is :status.', ['status' => $status->getLabel()]),
        };
    }

    private function date(CarbonInterface $date): string
    {
        return Localization::date($date->timezone(config('app.display_timezone')), 'd M Y, h:i A');
    }
}
