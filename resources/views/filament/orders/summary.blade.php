@php
    /** @var \App\Models\Order $order */
    $order = $getRecord();

    $events = collect([
        'Placed' => $order->created_at,
        'Confirmed' => $order->confirmed_at,
        'Shipped' => $order->shipped_at,
        'Delivered' => $order->delivered_at,
        'Cancelled' => $order->cancelled_at,
    ])->filter()->sort();

    $quantity = $order->items->sum('quantity');
@endphp

<div class="gx-order-summary">
    <div class="gx-order-stat gx-order-stat-total">
        <span class="gx-order-stat-label">Grand total</span>
        <span class="gx-order-stat-value">BDT {{ number_format((float) $order->grand_total, 2) }}</span>
        <span class="gx-order-stat-meta">
            {{ $quantity }} {{ str('item')->plural($quantity) }}
            @if ((float) $order->discount_total > 0)
                · BDT {{ number_format((float) $order->discount_total, 2) }} off
            @endif
        </span>
    </div>

    <div class="gx-order-stat" data-color="{{ $order->status->getColor() }}">
        <span class="gx-order-stat-label">Status</span>
        <span class="gx-order-stat-value">
            <x-filament::badge :color="$order->status->getColor()">{{ $order->status->getLabel() }}</x-filament::badge>
        </span>
        <span class="gx-order-stat-meta">
            {{ $events->keys()->last() }} {{ $events->last()->diffForHumans() }}
        </span>
    </div>

    <div class="gx-order-stat" data-color="{{ $order->payment_status->getColor() }}">
        <span class="gx-order-stat-label">Payment</span>
        <span class="gx-order-stat-value">
            <x-filament::badge :color="$order->payment_status->getColor()">{{ $order->payment_status->getLabel() }}</x-filament::badge>
        </span>
        <span class="gx-order-stat-meta">{{ $order->payment_method->getLabel() }}</span>
    </div>

    <div class="gx-order-stat">
        <span class="gx-order-stat-label">Courier</span>
        <span class="gx-order-stat-value gx-order-stat-text">{{ $order->courier_name ?: 'Not assigned' }}</span>
        <span class="gx-order-stat-meta">{{ $order->tracking_number ?: 'No tracking yet' }}</span>
    </div>
</div>
