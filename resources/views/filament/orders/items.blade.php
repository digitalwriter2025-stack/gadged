@php
    use App\Filament\Resources\Products\Tables\ProductsTable;
    use Illuminate\Support\Facades\Storage;

    /** @var \App\Models\Order $order */
    $order = $getRecord();
    $disk = Storage::disk(config('media-library.disk_name'));
@endphp

<div class="gx-order-items">
    <div class="gx-order-items-head" aria-hidden="true">
        <span>Product</span>
        <span>Unit price</span>
        <span>Qty</span>
        <span>Total</span>
    </div>

    <ul class="gx-order-items-list">
        @foreach ($order->items as $item)
            <li class="gx-order-item">
                <img
                    class="gx-order-item-thumb"
                    src="{{ $item->image_path ? $disk->url($item->image_path) : ProductsTable::PLACEHOLDER_IMAGE }}"
                    alt=""
                    loading="lazy"
                >

                <div class="gx-order-item-info">
                    <span class="gx-order-item-name">{{ $item->product_name }}</span>
                    <span class="gx-order-item-meta">
                        {{ collect(['SKU: '.$item->sku, $item->variant_name])->filter()->implode(' · ') }}
                    </span>
                </div>

                <span class="gx-order-item-price">
                    BDT {{ number_format((float) $item->unit_price, 2) }}<span class="gx-order-item-price-qty"> × {{ $item->quantity }}</span>
                </span>

                <span class="gx-order-item-qty">{{ $item->quantity }}</span>

                <span class="gx-order-item-total">BDT {{ number_format((float) $item->line_total, 2) }}</span>
            </li>
        @endforeach
    </ul>
</div>
