@php
    use App\Support\Money;

    $lines = $this->lines;
    $totals = $this->totals;
@endphp

<x-filament-panels::page>
    <style>
        .mo { display: grid; gap: 1.25rem; }
        .mo-head h1 { display: flex; align-items: center; gap: .6rem; margin: 0; font-size: 1.6rem; font-weight: 700; color: var(--gray-950); }
        .mo-head h1 svg { width: 1.75rem; height: 1.75rem; color: var(--primary-500); }
        .mo-head p { margin: .35rem 0 0; font-size: .875rem; color: var(--gray-600); }
        .mo-grid { display: grid; gap: 1.25rem; align-items: start; }
        @media (min-width: 1024px) { .mo-grid { grid-template-columns: 1fr 1fr; } }
        .mo-col { display: grid; gap: 1.25rem; }
        .mo-card { background: var(--gx-card-bg, #fff); border-radius: .85rem; box-shadow: var(--gx-card-shadow); outline: 1px solid var(--gx-card-ring); padding: 1.25rem 1.1rem; }
        .mo-card h2 { display: flex; align-items: center; gap: .5rem; margin: 0 0 1.1rem; font-size: 1.05rem; font-weight: 700; color: var(--gray-950); }
        .mo-card h2 svg { width: 1.2rem; height: 1.2rem; color: var(--primary-500); }
        .mo-fields { display: grid; gap: .9rem; grid-template-columns: 1fr; }
        @media (min-width: 640px) { .mo-fields { grid-template-columns: 1fr 1fr; } }
        .mo-span { grid-column: 1 / -1; }
        .mo-label { display: block; margin-bottom: .35rem; font-size: .78rem; color: var(--gray-600); }
        .mo-label b { color: var(--danger-600); font-weight: 500; }
        .mo-input { width: 100%; height: 2.8rem; padding: 0 .9rem; border: 1px solid var(--gray-200); border-radius: .5rem; background: var(--gx-card-bg, #fff); color: var(--gray-950); font-size: .9rem; }
        .mo-input:focus { outline: none; border-color: var(--primary-500); box-shadow: 0 0 0 3px color-mix(in oklab, var(--primary-500) 18%, transparent); }
        .mo-input[aria-invalid="true"] { border-color: var(--danger-500); }
        select.mo-input { cursor: pointer; }
        .mo-error { margin: .3rem 0 0; font-size: .75rem; color: var(--danger-600); }
        .mo-money { position: relative; max-width: 11rem; }
        .mo-money span { position: absolute; left: .9rem; top: 50%; transform: translateY(-50%); color: var(--gray-500); }
        .mo-money .mo-input { padding-left: 2rem; }
        .mo-search { position: relative; }
        .mo-search > svg { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); width: 1.1rem; height: 1.1rem; color: var(--gray-500); }
        .mo-search .mo-input { padding-left: 2.5rem; }
        .mo-results { position: absolute; z-index: 20; left: 0; right: 0; top: calc(100% + .35rem); max-height: 24rem; overflow-y: auto; background: var(--gx-card-bg, #fff); border: 1px solid var(--gray-200); border-radius: .6rem; box-shadow: 0 16px 32px -12px rgb(15 23 42 / .25); }
        .mo-result { display: flex; width: 100%; align-items: center; gap: .75rem; padding: .6rem .75rem; border: 0; border-bottom: 1px solid var(--gray-100); background: transparent; text-align: left; cursor: pointer; }
        .mo-result:last-child { border-bottom: 0; }
        .mo-result:hover:not(:disabled) { background: color-mix(in oklab, var(--primary-500) 7%, transparent); }
        .mo-result:disabled { cursor: not-allowed; opacity: .55; }
        .mo-thumb { flex: none; width: 3rem; height: 3rem; border-radius: .45rem; object-fit: contain; background: var(--gray-50); outline: 1px solid var(--gray-200); }
        .mo-thumb-empty { display: grid; place-items: center; color: var(--gray-400); }
        .mo-thumb-empty svg { width: 1.3rem; height: 1.3rem; }
        .mo-info { min-width: 0; flex: 1; }
        .mo-name { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: .875rem; font-weight: 600; color: var(--gray-950); }
        .mo-meta { display: block; font-size: .75rem; color: var(--gray-500); }
        .mo-price { flex: none; font-size: .875rem; font-weight: 700; color: var(--primary-600); }
        .mo-empty { padding: 2.5rem 0 1.5rem; text-align: center; font-size: .9rem; color: var(--gray-600); }
        .mo-lines { display: grid; gap: .6rem; margin-top: 1rem; }
        .mo-line { display: flex; align-items: center; gap: .75rem; padding: .6rem; border: 1px solid var(--gray-200); border-radius: .6rem; }
        .mo-qty { display: flex; flex: none; align-items: center; border: 1px solid var(--gray-200); border-radius: .45rem; }
        .mo-qty button { width: 1.9rem; height: 1.9rem; border: 0; background: transparent; color: var(--gray-700); font-size: 1rem; cursor: pointer; }
        .mo-qty input { width: 2.6rem; height: 1.9rem; border: 0; background: transparent; text-align: center; font-size: .85rem; color: var(--gray-950); -moz-appearance: textfield; }
        .mo-qty input::-webkit-inner-spin-button { -webkit-appearance: none; }
        .mo-remove { flex: none; border: 0; background: transparent; color: var(--danger-600); cursor: pointer; padding: .25rem; }
        .mo-remove svg { width: 1.1rem; height: 1.1rem; }
        .mo-totals { display: grid; gap: .4rem; margin-top: 1rem; padding-top: .9rem; border-top: 1px dashed var(--gray-200); font-size: .875rem; color: var(--gray-700); }
        .mo-totals div { display: flex; justify-content: space-between; }
        .mo-totals .mo-grand { font-size: 1rem; font-weight: 700; color: var(--gray-950); }
        .mo-submit { display: flex; width: 100%; align-items: center; justify-content: center; gap: .6rem; height: 3.3rem; border: 0; border-radius: .6rem; background: var(--primary-500); color: #fff; font-size: 1rem; font-weight: 700; cursor: pointer; transition: background .15s; }
        .mo-submit:hover { background: var(--primary-600); }
        .mo-submit:disabled { opacity: .7; cursor: wait; }
        .mo-submit svg { width: 1.25rem; height: 1.25rem; }
        .dark .mo-head h1, .dark .mo-card h2, .dark .mo-name, .dark .mo-input, .dark .mo-qty input, .dark .mo-totals .mo-grand { color: #fff; }
        .dark .mo-head p, .dark .mo-label, .dark .mo-empty, .dark .mo-totals { color: var(--gray-400); }
        .dark .mo-input, .dark .mo-results, .dark .mo-line, .dark .mo-qty, .dark .mo-totals { border-color: var(--gray-700); }
        .dark .mo-result { border-color: var(--gray-800); }
        .dark .mo-qty button { color: var(--gray-300); }
        .dark .mo-thumb { background: var(--gray-800); outline-color: var(--gray-700); }
    </style>

    <form wire:submit="placeOrder" class="mo">
        <div class="mo-head">
            <h1>
                <x-filament::icon icon="heroicon-o-shopping-cart" />
                Manual Order
            </h1>
            <p>Create orders received by phone or Facebook message manually from here</p>
        </div>

        <div class="mo-grid">
            <div class="mo-col">
                <section class="mo-card">
                    <h2><x-filament::icon icon="heroicon-o-user" /> Customer Information</h2>

                    <div class="mo-fields">
                        <div>
                            <label for="mo-name" class="mo-label">Name <b>*</b></label>
                            <input id="mo-name" type="text" wire:model="customerName" placeholder="Customer name" class="mo-input" @error('customerName') aria-invalid="true" @enderror>
                            @error('customerName') <p class="mo-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="mo-phone" class="mo-label">Phone Number <b>*</b></label>
                            <input id="mo-phone" type="tel" wire:model="customerPhone" placeholder="01XXXXXXXXX" class="mo-input" @error('customerPhone') aria-invalid="true" @enderror>
                            @error('customerPhone') <p class="mo-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="mo-span">
                            <label for="mo-address" class="mo-label">Address <b>*</b></label>
                            <input id="mo-address" type="text" wire:model="shippingAddress" placeholder="Full address" class="mo-input" @error('shippingAddress') aria-invalid="true" @enderror>
                            @error('shippingAddress') <p class="mo-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="mo-location" class="mo-label">Delivery Location @if ($this->shippingMethods->isNotEmpty()) <b>*</b> @endif</label>
                            <select id="mo-location" wire:model.live="shippingMethodId" class="mo-input" @error('shippingMethodId') aria-invalid="true" @enderror>
                                <option value="">Select</option>
                                @foreach ($this->shippingMethods as $method)
                                    <option value="{{ $method->id }}">{{ $method->name }} — {{ Money::format($method->charge) }}</option>
                                @endforeach
                            </select>
                            @error('shippingMethodId') <p class="mo-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="mo-payment" class="mo-label">Payment Method</label>
                            <select id="mo-payment" wire:model="paymentMethod" class="mo-input">
                                @foreach ($this->paymentMethods as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('paymentMethod') <p class="mo-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="mo-span">
                            <label for="mo-note" class="mo-label">Note (optional)</label>
                            <input id="mo-note" type="text" wire:model="customerNote" placeholder="Facebook message / special instructions" class="mo-input">
                            @error('customerNote') <p class="mo-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <section class="mo-card">
                    <h2><x-filament::icon icon="heroicon-o-tag" /> Extra Discount</h2>

                    <div class="mo-money">
                        <span>৳</span>
                        <input type="number" min="0" step="1" wire:model.live.debounce.400ms="extraDiscount" aria-label="Extra Discount" class="mo-input" @error('extraDiscount') aria-invalid="true" @enderror>
                    </div>
                    @error('extraDiscount') <p class="mo-error">{{ $message }}</p> @enderror
                </section>
            </div>

            <section class="mo-card">
                <h2><x-filament::icon icon="heroicon-o-magnifying-glass" /> Add Products</h2>

                <div class="mo-search" x-data="{ open: true }" x-on:click.outside="open = false">
                    <x-filament::icon icon="heroicon-o-magnifying-glass" />
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        x-on:focus="open = true"
                        x-on:input="open = true"
                        x-on:keydown.escape="open = false"
                        x-on:keydown.enter.prevent
                        placeholder="Type a product name, model or SKU..."
                        autocomplete="off"
                        aria-label="Search products"
                        class="mo-input"
                    >

                    @if (mb_strlen(trim($search)) >= 2)
                        <div class="mo-results" x-show="open">
                            @forelse ($this->searchResults as $result)
                                <button
                                    type="button"
                                    wire:key="result-{{ $result['variant_id'] }}"
                                    wire:click="addProduct({{ $result['variant_id'] }})"
                                    class="mo-result"
                                    @disabled(! $result['available'])
                                >
                                    @if ($result['image'])
                                        <img src="{{ $result['image'] }}" alt="" loading="lazy" class="mo-thumb">
                                    @else
                                        <span class="mo-thumb mo-thumb-empty"><x-filament::icon icon="heroicon-o-photo" /></span>
                                    @endif
                                    <span class="mo-info">
                                        <span class="mo-name">{{ $result['name'] }}@if ($result['variant']) — {{ $result['variant'] }}@endif</span>
                                        <span class="mo-meta">
                                            @if ($result['sku']) SKU: {{ $result['sku'] }} · @endif
                                            {{ $result['available'] ? 'Stock: '.$result['stock'] : 'Out of stock' }}
                                        </span>
                                    </span>
                                    <span class="mo-price">{{ $result['price'] }}</span>
                                </button>
                            @empty
                                <p class="mo-empty" style="padding: 1rem">No products found</p>
                            @endforelse
                        </div>
                    @endif
                </div>

                @if ($items === [])
                    <p class="mo-empty">No products added yet</p>
                @else
                    <div class="mo-lines">
                        @foreach ($items as $index => $item)
                            @php $line = $lines->get($item['variant_id']); @endphp
                            <div class="mo-line" wire:key="line-{{ $item['variant_id'] }}">
                                @php $image = $line ? $this->imageUrl($line->variant) : null; @endphp
                                @if ($image)
                                    <img src="{{ $image }}" alt="" class="mo-thumb">
                                @else
                                    <span class="mo-thumb mo-thumb-empty"><x-filament::icon icon="heroicon-o-photo" /></span>
                                @endif
                                <span class="mo-info">
                                    @if ($line)
                                        <span class="mo-name">{{ $line->product->name }}@if ($line->product->isVariable()) — {{ $line->variant->name }}@endif</span>
                                        <span class="mo-meta">{{ Money::format($line->unitPrice) }} × {{ $line->quantity }} = <strong>{{ Money::format($line->lineTotal()) }}</strong></span>
                                    @else
                                        <span class="mo-name">This product is no longer available</span>
                                        <span class="mo-meta">Please remove it from the list</span>
                                    @endif
                                </span>
                                <span class="mo-qty">
                                    <button type="button" wire:click="changeQuantity({{ $index }}, -1)" aria-label="Decrease">−</button>
                                    <input type="number" min="1" wire:model.live.debounce.400ms="items.{{ $index }}.quantity" aria-label="Quantity">
                                    <button type="button" wire:click="changeQuantity({{ $index }}, 1)" aria-label="Increase">+</button>
                                </span>
                                <button type="button" wire:click="removeItem({{ $index }})" class="mo-remove" aria-label="Remove">
                                    <x-filament::icon icon="heroicon-o-trash" />
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <div class="mo-totals">
                        <div><span>Subtotal</span><span>{{ Money::format($totals['subtotal']) }}</span></div>
                        <div><span>Delivery Charge</span><span>{{ Money::format($totals['shipping']) }}</span></div>
                        @if (Money::isPositive($totals['discount']))
                            <div><span>Extra Discount</span><span>− {{ Money::format($totals['discount']) }}</span></div>
                        @endif
                        <div class="mo-grand"><span>Total</span><span>{{ Money::format($totals['total']) }}</span></div>
                    </div>
                @endif

                @error('items') <p class="mo-error">{{ $message }}</p> @enderror
            </section>
        </div>

        <button type="submit" class="mo-submit" wire:loading.attr="disabled" wire:target="placeOrder">
            <x-filament::icon icon="heroicon-o-shopping-cart" wire:loading.remove wire:target="placeOrder" />
            <x-filament::loading-indicator wire:loading wire:target="placeOrder" />
            Place Order
        </button>
    </form>
</x-filament-panels::page>
