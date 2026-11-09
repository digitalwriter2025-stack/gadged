@php
    $inputClass = 'mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition focus:border-builder focus:ring-2 focus:ring-builder/20 focus:outline-none';
    $labelClass = 'block text-xs font-medium text-gray-700';
    $errorClass = 'mt-1 text-[11px] text-price';
    $selectedDistrict = (int) old('district_id');
    $selectedShippingMethod = (int) old('shipping_method_id', count($shippingMethods) === 1 ? $shippingMethods[0]['id'] : 0);
    $selectedPayment = old('payment_method', $paymentMethods[0]['value'] ?? null);
@endphp

<x-layouts.store :title="$seo['title']" :description="$seo['description']">
    <x-slot:head>
        <meta name="robots" content="noindex, nofollow">
    </x-slot:head>

    <x-store.breadcrumbs :items="$breadcrumbs" />

    <div class="bg-surface py-4 lg:py-6" data-checkout data-currency-symbol="{{ $currencySymbol }}" data-subtotal="{{ $subtotalAmount }}">
        <div class="mx-auto max-w-store px-4">
            <h1 class="mb-4 text-base font-semibold text-gray-900 lg:text-lg">{{ __('Checkout') }}</h1>

            @error('cart')
                <p class="mb-4 rounded-md border border-price/30 bg-price/5 px-3 py-2.5 text-xs font-medium text-price" role="alert">{{ $message }}</p>
            @enderror
            @error('product_id')
                <p class="mb-4 rounded-md border border-price/30 bg-price/5 px-3 py-2.5 text-xs font-medium text-price" role="alert">{{ $message }}</p>
            @enderror

            @if ($lines === [])
                <section class="rounded-md border border-gray-200 bg-white px-4 py-12 text-center">
                    <span class="mx-auto grid size-14 place-items-center rounded-full bg-builder/10 text-builder">
                        <x-store.icon name="cart" class="size-7" />
                    </span>
                    <h2 class="mt-4 text-sm font-semibold text-gray-900">{{ __('Your cart is empty') }}</h2>
                    <p class="mt-1 text-xs text-gray-500">{{ __('Add a product to your cart to place an order.') }}</p>
                    <a href="{{ route('store.home') }}" class="mt-5 inline-flex h-10 items-center gap-2 rounded-md bg-button px-5 text-sm font-semibold text-button-text transition hover:bg-button-hover">
                        <x-store.icon name="arrow-left" class="size-4" /> {{ __('Continue Shopping') }}
                    </a>
                </section>
            @else
                <div class="grid grid-cols-1 items-start gap-4 lg:grid-cols-[minmax(0,1fr)_22rem]">
                    <div class="min-w-0 space-y-4">
                        <section class="rounded-md border border-gray-200 bg-white" aria-labelledby="cart-heading">
                            <h2 id="cart-heading" class="border-b border-gray-200 px-4 py-3 text-sm font-semibold text-gray-900">
                                {{ __('Your Cart') }} <span class="font-normal text-gray-500">({{ trans_choice(':count item|:count items', $itemCount, ['count' => \App\Support\Localization::digits($itemCount)]) }})</span>
                            </h2>

                            <ul class="divide-y divide-gray-100">
                                @foreach ($lines as $line)
                                    <li class="flex gap-3 p-4">
                                        <a href="{{ $line['url'] }}" class="grid size-16 shrink-0 place-items-center overflow-hidden rounded-md border border-gray-200 bg-white p-1 sm:size-20">
                                            <img src="{{ $line['image'] }}" alt="" width="80" height="80" class="size-full object-contain">
                                        </a>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-2">
                                                <a href="{{ $line['url'] }}" class="line-clamp-2 text-xs font-medium text-gray-900 transition hover:text-builder sm:text-sm">{{ $line['name'] }}</a>

                                                <form method="POST" action="{{ route('store.cart.destroy', $line['variant_id']) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="grid size-8 shrink-0 cursor-pointer place-items-center rounded-md text-gray-400 transition hover:bg-price/5 hover:text-price" aria-label="{{ __('Remove :name', ['name' => $line['name']]) }}">
                                                        <x-store.icon name="trash" class="size-4" />
                                                    </button>
                                                </form>
                                            </div>

                                            <p class="mt-0.5 text-[11px] text-gray-500">
                                                {{ __('SKU') }}: {{ $line['sku'] }}
                                                @if ($line['is_deal'])
                                                    <span class="ml-1 rounded bg-accent/10 px-1.5 py-0.5 font-semibold text-accent">{{ __('Flash Deal') }}</span>
                                                @endif
                                            </p>

                                            <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                                                <div class="flex h-8 items-center rounded-md border border-gray-300">
                                                    <form method="POST" action="{{ route('store.cart.update', $line['variant_id']) }}" class="h-full">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="quantity" value="{{ $line['quantity'] - 1 }}">
                                                        <button type="submit" class="grid h-full w-8 cursor-pointer place-items-center text-gray-600 transition hover:text-accent" aria-label="{{ __('Decrease quantity of :name', ['name' => $line['name']]) }}">
                                                            <x-store.icon name="minus" class="size-3.5" />
                                                        </button>
                                                    </form>
                                                    <span class="grid h-full w-9 place-items-center border-x border-gray-300 text-xs font-medium" aria-label="{{ __('Quantity') }}">{{ \App\Support\Localization::digits($line['quantity']) }}</span>
                                                    <form method="POST" action="{{ route('store.cart.update', $line['variant_id']) }}" class="h-full">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="quantity" value="{{ $line['quantity'] + 1 }}">
                                                        <button type="submit" @disabled($line['quantity'] >= $maxQuantity) class="grid h-full w-8 cursor-pointer place-items-center text-gray-600 transition hover:text-accent disabled:cursor-not-allowed disabled:opacity-40" aria-label="{{ __('Increase quantity of :name', ['name' => $line['name']]) }}">
                                                            <x-store.icon name="plus" class="size-3.5" />
                                                        </button>
                                                    </form>
                                                </div>

                                                <p class="text-right leading-tight">
                                                    <span class="block text-[11px] text-gray-500">
                                                        {{ \App\Support\Localization::digits($line['quantity']) }} × {{ $line['unit_price'] }}
                                                        @if ($line['regular_price'])
                                                            <del class="ml-1 text-gray-400">{{ $line['regular_price'] }}</del>
                                                        @endif
                                                    </span>
                                                    <span class="text-sm font-bold text-price">{{ $line['line_total'] }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </section>

                        <section class="rounded-md border border-gray-200 bg-white" aria-labelledby="delivery-heading">
                            <h2 id="delivery-heading" class="border-b border-gray-200 px-4 py-3 text-sm font-semibold text-gray-900">{{ __('Delivery Information') }}</h2>

                            <form id="checkout-form" method="POST" action="{{ route('store.checkout.store') }}" class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2" data-checkout-form>
                                @csrf

                                <div>
                                    <label for="customer_name" class="{{ $labelClass }}">{{ __('Full Name') }} <span class="text-price">*</span></label>
                                    <input id="customer_name" name="customer_name" type="text" value="{{ old('customer_name') }}" required maxlength="150" autocomplete="name" placeholder="{{ __('Your full name') }}" class="{{ $inputClass }}" @error('customer_name') aria-invalid="true" @enderror>
                                    @error('customer_name') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="customer_phone" class="{{ $labelClass }}">{{ __('Mobile Number') }} <span class="text-price">*</span></label>
                                    <input id="customer_phone" name="customer_phone" type="tel" value="{{ old('customer_phone') }}" required maxlength="20" inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX" class="{{ $inputClass }}" @error('customer_phone') aria-invalid="true" @enderror>
                                    @error('customer_phone') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="district_id" class="{{ $labelClass }}">{{ __('District') }} <span class="text-price">*</span></label>
                                    <select id="district_id" name="district_id" required class="{{ $inputClass }} cursor-pointer" @error('district_id') aria-invalid="true" @enderror>
                                        <option value="">{{ __('Select your district') }}</option>
                                        @foreach ($districts as $district)
                                            <option value="{{ $district['id'] }}" @selected($selectedDistrict === $district['id'])>{{ $district['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('district_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="shipping_area" class="{{ $labelClass }}">{{ __('Area / Thana') }}</label>
                                    <input id="shipping_area" name="shipping_area" type="text" value="{{ old('shipping_area') }}" maxlength="150" placeholder="{{ __('e.g. Mirpur') }}" class="{{ $inputClass }}" @error('shipping_area') aria-invalid="true" @enderror>
                                    @error('shipping_area') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="shipping_address" class="{{ $labelClass }}">{{ __('Full Address') }} <span class="text-price">*</span></label>
                                    <textarea id="shipping_address" name="shipping_address" rows="2" required maxlength="500" autocomplete="street-address" placeholder="{{ __('House, road, area') }}" class="{{ $inputClass }}" @error('shipping_address') aria-invalid="true" @enderror>{{ old('shipping_address') }}</textarea>
                                    @error('shipping_address') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                                </div>

                                @if ($shippingMethods !== [])
                                    <fieldset class="sm:col-span-2">
                                        <legend class="{{ $labelClass }}">{{ __('Delivery Method') }} <span class="text-price">*</span></legend>
                                        <div class="mt-1 grid grid-cols-1 gap-2 sm:grid-cols-2">
                                            @foreach ($shippingMethods as $method)
                                                <label class="flex cursor-pointer items-start gap-2.5 rounded-md border border-gray-200 px-3 py-2.5 transition has-checked:border-builder has-checked:bg-builder/5">
                                                    <input type="radio" name="shipping_method_id" value="{{ $method['id'] }}" required data-shipping-method data-shipping="{{ $method['shipping_amount'] }}" @checked($selectedShippingMethod === $method['id']) class="mt-0.5 size-4 shrink-0 accent-builder">
                                                    <span class="min-w-0 flex-1">
                                                        <span class="flex items-start justify-between gap-2">
                                                            <span class="text-xs font-semibold text-gray-900">{{ $method['name'] }}</span>
                                                            <span class="shrink-0 text-xs font-bold text-price">{{ $method['charge_label'] }}</span>
                                                        </span>
                                                        @if ($method['description'])
                                                            <span class="mt-0.5 block text-[11px] text-gray-500">{{ $method['description'] }}</span>
                                                        @endif
                                                        @if ($method['estimate'])
                                                            <span class="mt-0.5 block text-[11px] font-medium text-gray-600">{{ $method['estimate'] }}</span>
                                                        @endif
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('shipping_method_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                                    </fieldset>
                                @endif

                                <div>
                                    <label for="customer_email" class="{{ $labelClass }}">{{ __('Email') }} <span class="font-normal text-gray-400">({{ __('optional') }})</span></label>
                                    <input id="customer_email" name="customer_email" type="email" value="{{ old('customer_email') }}" maxlength="191" autocomplete="email" placeholder="you@example.com" class="{{ $inputClass }}" @error('customer_email') aria-invalid="true" @enderror>
                                    @error('customer_email') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="customer_note" class="{{ $labelClass }}">{{ __('Order Note') }} <span class="font-normal text-gray-400">({{ __('optional') }})</span></label>
                                    <input id="customer_note" name="customer_note" type="text" value="{{ old('customer_note') }}" maxlength="1000" placeholder="{{ __('Anything we should know?') }}" class="{{ $inputClass }}" @error('customer_note') aria-invalid="true" @enderror>
                                    @error('customer_note') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                                </div>
                            </form>
                        </section>
                    </div>

                    <aside class="min-w-0 lg:sticky lg:top-28" aria-labelledby="summary-heading">
                        <section class="rounded-md border border-gray-200 bg-white">
                            <h2 id="summary-heading" class="border-b border-gray-200 px-4 py-3 text-sm font-semibold text-gray-900">{{ __('Order Summary') }}</h2>

                            <dl class="space-y-2 px-4 py-3 text-xs">
                                <div class="flex justify-between gap-3">
                                    <dt class="text-gray-600">{{ __('Subtotal') }}</dt>
                                    <dd class="font-medium text-gray-900">{{ $subtotal }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-gray-600">{{ __('Delivery Charge') }}</dt>
                                    <dd class="font-medium text-gray-900" data-shipping-total>{{ $shippingMethods === [] ? __('Free') : __('Select delivery method') }}</dd>
                                </div>
                                <div class="flex justify-between gap-3 border-t border-dashed border-gray-200 pt-2 text-sm">
                                    <dt class="font-semibold text-gray-900">{{ __('Total') }}</dt>
                                    <dd class="font-bold text-price" data-grand-total>{{ $subtotal }}</dd>
                                </div>
                            </dl>

                            <fieldset class="border-t border-gray-200 px-4 py-3">
                                <legend class="sr-only">{{ __('Payment Method') }}</legend>
                                <p class="mb-2 text-xs font-semibold text-gray-900">{{ __('Payment Method') }}</p>
                                <div class="space-y-2">
                                    @foreach ($paymentMethods as $method)
                                        <label class="flex cursor-pointer items-center gap-2 rounded-md border border-gray-200 px-3 py-2.5 text-xs font-medium text-gray-800 transition has-checked:border-builder has-checked:bg-builder/5">
                                            <input type="radio" name="payment_method" value="{{ $method['value'] }}" form="checkout-form" @checked($selectedPayment === $method['value']) class="size-4 accent-builder">
                                            {{ $method['label'] }}
                                        </label>
                                    @endforeach
                                </div>
                                @error('payment_method') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                            </fieldset>

                            <div class="border-t border-gray-200 p-4">
                                @if ($minimumOrder)
                                    <p class="mb-3 rounded-md bg-amber-50 px-3 py-2 text-[11px] text-amber-800">{{ __('The minimum order amount is :amount.', ['amount' => $minimumOrder]) }}</p>
                                @endif

                                <button type="submit" form="checkout-form" data-ripple data-place-order @disabled($minimumOrder) class="relative flex h-11 w-full cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-md bg-button px-5 text-sm font-semibold text-button-text transition hover:bg-button-hover active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-gray-400">
                                    <x-store.icon name="check" class="size-4" /> {{ __('Place Order') }}
                                </button>
                                <p class="mt-2 text-center text-[11px] text-gray-500">{{ __('Our team will call you to confirm the order.') }}</p>
                            </div>
                        </section>

                        <a href="{{ route('store.home') }}" class="mt-3 flex items-center justify-center gap-1.5 text-xs font-medium text-builder hover:underline">
                            <x-store.icon name="arrow-left" class="size-3.5" /> {{ __('Continue Shopping') }}
                        </a>
                    </aside>
                </div>
            @endif
        </div>
    </div>
</x-layouts.store>
