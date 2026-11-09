@php
    $inputClass = 'mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition focus:border-builder focus:ring-2 focus:ring-builder/20 focus:outline-none aria-invalid:border-price';
    $labelClass = 'block text-xs font-medium text-gray-700';
    $errorClass = 'mt-1 text-[11px] text-price';
@endphp

<x-layouts.store :title="__('Track Your Order').' | '.config('app.name')" :description="__('Check the delivery status of your :store order with your order number and the mobile number you used at checkout.', ['store' => config('app.name')])">
    <x-slot:head>
        <link rel="canonical" href="{{ route('store.orders.track') }}">
        @if ($tracking)
            <meta name="robots" content="noindex, nofollow">
        @endif
    </x-slot:head>

    <x-store.breadcrumbs :items="$breadcrumbs" container="max-w-hero" />

    <div class="bg-surface py-6 lg:py-10" data-order-tracking>
        <div class="mx-auto grid max-w-5xl gap-4 px-4 lg:grid-cols-[22rem_minmax(0,1fr)] lg:items-start lg:gap-6">
            <section class="rounded-md border border-gray-200 bg-white p-4 sm:p-6" aria-labelledby="track-heading">
                <h1 id="track-heading" class="text-lg font-bold text-gray-900 lg:text-xl">{{ __('Track Your Order') }}</h1>
                <p class="mt-1.5 text-[13px] leading-5 text-gray-600">{{ __('Enter the order number from your confirmation page or SMS and the mobile number you ordered with.') }}</p>

                <form method="POST" action="{{ route('store.orders.lookup') }}" class="mt-5 space-y-4" data-submit-once>
                    @csrf

                    <div>
                        <label for="order_number" class="{{ $labelClass }}">{{ __('Order Number') }} <span class="text-price">*</span></label>
                        <input id="order_number" name="order_number" type="text" value="{{ old('order_number', $tracking['number'] ?? '') }}" required maxlength="30" autocomplete="off" autocapitalize="characters" spellcheck="false" placeholder="{{ __('e.g. :example', ['example' => 'GD261108-0001']) }}" class="{{ $inputClass }} uppercase placeholder:normal-case" @error('order_number') aria-invalid="true" aria-describedby="order_number-error" @enderror>
                        @error('order_number') <p id="order_number-error" class="{{ $errorClass }}" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="{{ $labelClass }}">{{ __('Mobile Number') }} <span class="text-price">*</span></label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required maxlength="20" inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX" class="{{ $inputClass }}" @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>
                        @error('phone') <p id="phone-error" class="{{ $errorClass }}" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" data-ripple class="relative flex h-11 w-full cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-md bg-button px-5 text-sm font-semibold text-button-text transition hover:bg-button-hover active:scale-[0.98] disabled:cursor-wait disabled:opacity-70">
                        <x-store.icon name="truck" class="size-4" /> {{ __('Track Order') }}
                    </button>
                </form>

                @if ($contact['hotline'])
                    <p class="mt-5 border-t border-gray-100 pt-4 text-xs leading-5 text-gray-500">
                        {!! __("Can't find your order number? Call :hotline and we will look it up for you.", ['hotline' => '<a href="tel:'.e($contact['hotline_tel']).'" class="font-semibold text-gray-900 hover:text-accent">'.e($contact['hotline']).'</a>']) !!}
                    </p>
                @endif
            </section>

            @if ($tracking)
                <section class="rounded-md border border-gray-200 bg-white" aria-labelledby="tracking-result-heading" data-tracking-result>
                    <header class="flex flex-wrap items-start justify-between gap-3 border-b border-gray-100 px-4 py-4 sm:px-6">
                        <div>
                            <p class="text-[11px] font-semibold tracking-wider text-gray-500 uppercase">{{ __('Order') }}</p>
                            <h2 id="tracking-result-heading" class="text-base font-bold text-gray-900 sm:text-lg">#{{ $tracking['number'] }}</h2>
                            <p class="mt-0.5 text-xs text-gray-500">{{ __('Placed on :date', ['date' => $tracking['placed_at']]) }}</p>
                        </div>
                        <p @class([
                            'rounded-full px-3 py-1 text-xs font-semibold',
                            'bg-price/10 text-price' => $tracking['is_closed'],
                            'bg-savings/10 text-savings' => ! $tracking['is_closed'],
                        ])>{{ $tracking['status'] }}</p>
                    </header>

                    @if ($tracking['closed_message'])
                        <p class="mx-4 mt-4 rounded-md bg-price/5 px-3 py-2.5 text-xs leading-5 text-gray-800 sm:mx-6" role="status">{{ $tracking['closed_message'] }}</p>
                    @endif

                    <ol class="px-4 py-5 sm:px-6" aria-label="{{ __('Delivery progress') }}">
                        @foreach ($tracking['steps'] as $step)
                            <li class="relative flex gap-3 pb-5 last:pb-0" @if ($step['state'] === 'current') aria-current="step" @endif>
                                @unless ($loop->last)
                                    <span @class([
                                        'absolute top-6 left-[11px] h-[calc(100%-1.25rem)] w-0.5',
                                        'bg-savings' => $step['state'] === 'done',
                                        'bg-gray-200' => $step['state'] !== 'done',
                                    ]) aria-hidden="true"></span>
                                @endunless
                                <span @class([
                                    'relative grid size-6 shrink-0 place-items-center rounded-full border-2',
                                    'border-savings bg-savings text-white' => $step['state'] === 'done',
                                    'border-accent bg-white text-accent' => $step['state'] === 'current',
                                    'border-gray-300 bg-white text-gray-300' => $step['state'] === 'upcoming',
                                ]) aria-hidden="true">
                                    @if ($step['state'] === 'done')
                                        <x-store.icon name="check" class="size-3.5" />
                                    @else
                                        <span class="size-2 rounded-full bg-current"></span>
                                    @endif
                                </span>
                                <div class="min-w-0 pt-0.5">
                                    <p @class([
                                        'text-sm font-semibold',
                                        'text-gray-900' => $step['state'] !== 'upcoming',
                                        'text-gray-400' => $step['state'] === 'upcoming',
                                    ])>
                                        {{ $step['label'] }}
                                        <span class="sr-only">({{ $step['state'] === 'done' ? __('completed') : ($step['state'] === 'current' ? __('in progress') : __('not started')) }})</span>
                                    </p>
                                    @if ($step['state'] !== 'upcoming')
                                        <p class="mt-0.5 text-xs leading-5 text-gray-600">{{ $step['description'] }}</p>
                                    @endif
                                    @if ($step['date'])
                                        <p class="mt-0.5 text-[11px] text-gray-400"><time>{{ $step['date'] }}</time></p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>

                    <dl class="grid grid-cols-2 gap-x-4 gap-y-3 border-t border-gray-100 px-4 py-4 text-xs sm:grid-cols-4 sm:px-6">
                        <div>
                            <dt class="text-gray-500">{{ __('Total') }}</dt>
                            <dd class="mt-0.5 font-semibold text-gray-900">{{ $tracking['grand_total'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">{{ __('Payment') }}</dt>
                            <dd class="mt-0.5 font-semibold text-gray-900">{{ $tracking['payment_method'] }} · {{ $tracking['payment_status'] }}</dd>
                        </div>
                        @if ($tracking['district'])
                            <div>
                                <dt class="text-gray-500">{{ __('Delivery to') }}</dt>
                                <dd class="mt-0.5 font-semibold text-gray-900">{{ $tracking['district'] }}</dd>
                            </div>
                        @endif
                        @if ($tracking['courier'])
                            <div>
                                <dt class="text-gray-500">{{ __('Courier') }}</dt>
                                <dd class="mt-0.5 font-semibold text-gray-900">
                                    {{ $tracking['courier'] }}
                                    @if ($tracking['tracking_number'])
                                        <span class="block font-normal text-gray-600">{{ __('ID') }}: <span class="font-mono">{{ $tracking['tracking_number'] }}</span></span>
                                    @endif
                                </dd>
                            </div>
                        @endif
                    </dl>

                    <div class="border-t border-gray-100 px-4 py-4 sm:px-6">
                        <h3 class="text-[11px] font-semibold tracking-wider text-gray-500 uppercase">{{ __('Items in this order') }}</h3>
                        <ul class="mt-2 divide-y divide-gray-100 text-[13px]">
                            @foreach ($tracking['items'] as $item)
                                <li class="flex items-start justify-between gap-3 py-2">
                                    <span class="min-w-0 text-gray-800">
                                        {{ $item['name'] }}
                                        @if ($item['variant'])
                                            <span class="block text-[11px] text-gray-500">{{ $item['variant'] }}</span>
                                        @endif
                                    </span>
                                    <span class="shrink-0 text-gray-500">× {{ \App\Support\Localization::digits($item['quantity']) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            @else
                <section class="rounded-md border border-gray-200 bg-white p-4 sm:p-6" aria-labelledby="track-help-heading">
                    <h2 id="track-help-heading" class="text-sm font-bold text-gray-900">{{ __('How delivery works') }}</h2>
                    <ol class="mt-4 grid gap-4 text-[13px] sm:grid-cols-2">
                        <li class="flex gap-3">
                            <span class="grid size-8 shrink-0 place-items-center rounded-full bg-surface text-gray-700"><x-store.icon name="phone" class="size-4" /></span>
                            <span><span class="block font-semibold text-gray-900">{{ __('We call to confirm') }}</span><span class="text-xs leading-5 text-gray-600">{{ __('A team member calls the number on your order before it is processed.') }}</span></span>
                        </li>
                        <li class="flex gap-3">
                            <span class="grid size-8 shrink-0 place-items-center rounded-full bg-surface text-gray-700"><x-store.icon name="package" class="size-4" /></span>
                            <span><span class="block font-semibold text-gray-900">{{ __('Checked and packed') }}</span><span class="text-xs leading-5 text-gray-600">{{ __('Products are checked and packed. You can follow each step here.') }}</span></span>
                        </li>
                        <li class="flex gap-3">
                            <span class="grid size-8 shrink-0 place-items-center rounded-full bg-surface text-gray-700"><x-store.icon name="truck" class="size-4" /></span>
                            <span><span class="block font-semibold text-gray-900">{{ __('Handed to the courier') }}</span><span class="text-xs leading-5 text-gray-600">{{ __('Once shipped, the courier name and consignment ID appear on this page.') }}</span></span>
                        </li>
                        <li class="flex gap-3">
                            <span class="grid size-8 shrink-0 place-items-center rounded-full bg-surface text-gray-700"><x-store.icon name="cash" class="size-4" /></span>
                            <span><span class="block font-semibold text-gray-900">{{ __('Pay on delivery') }}</span><span class="text-xs leading-5 text-gray-600">{{ __('With cash on delivery, check your parcel and pay the courier.') }}</span></span>
                        </li>
                    </ol>
                </section>
            @endif
        </div>
    </div>
</x-layouts.store>
