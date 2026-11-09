<x-layouts.store :title="$seo['title']" :description="$seo['description']">
    <x-slot:head>
        <meta name="robots" content="noindex, nofollow">
        <style>
            @media print {
                @page { size: A4; margin: 12mm; }
                body { padding: 0 !important; background: #fff !important; }
                body > :not(main) { display: none !important; }
                * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            }
        </style>
    </x-slot:head>

    <div class="bg-surface py-6 lg:py-10 print:bg-white print:py-0" data-confetti-on-load>
        <div class="mx-auto max-w-3xl px-4 print:max-w-none print:px-0">
            <section class="rounded-md border border-gray-200 bg-white px-4 py-6 text-center sm:px-8 print:hidden" aria-labelledby="thank-you-heading">
                <span class="mx-auto grid size-14 place-items-center rounded-full bg-savings/10 text-savings">
                    <x-store.icon name="check" class="size-7" />
                </span>
                <h1 id="thank-you-heading" class="mt-3 text-lg font-bold text-gray-900 sm:text-xl">{{ __('Thank you for your order!') }}</h1>
                <p class="mt-2 text-sm text-gray-600">{!! __('Your order has been placed successfully. Our team will call you at :phone to confirm it.', ['phone' => '<span class="font-semibold text-gray-900">'.e($order['customer_phone']).'</span>']) !!}</p>
            </section>

            <article class="mt-4 rounded-md border border-gray-200 bg-white text-xs text-gray-700 print:mt-0 print:rounded-none print:border-0 print:text-[11px]" aria-labelledby="invoice-heading" data-invoice>
                {{-- Seller & invoice meta --}}
                <header class="flex flex-col gap-5 border-b-2 border-gray-900 px-4 py-5 sm:flex-row sm:items-start sm:justify-between sm:px-6 print:px-0">
                    <div class="min-w-0 space-y-1">
                        <x-store.logo class="mb-2 h-9 w-auto max-w-[200px] object-contain object-left text-gray-900" />
                        @if ($contact['address'])
                            <p class="max-w-xs text-gray-600">{{ $contact['address'] }}</p>
                        @endif
                        @if ($contact['hotline'])
                            <p class="text-gray-600">{{ __('Phone') }}: <span class="text-gray-900">{{ $contact['hotline'] }}</span></p>
                        @endif
                        @if ($contact['email'])
                            <p class="text-gray-600">{{ __('Email') }}: <span class="text-gray-900">{{ $contact['email'] }}</span></p>
                        @endif
                    </div>

                    <div class="sm:text-right">
                        <h2 id="invoice-heading" class="text-2xl font-extrabold tracking-[0.2em] text-gray-900 uppercase">{{ __('Invoice') }}</h2>
                        <dl class="mt-2 grid grid-cols-[auto_1fr] gap-x-3 gap-y-1 sm:justify-end">
                            <dt class="text-gray-500 sm:text-right">{{ __('Invoice No:') }}</dt>
                            <dd class="font-bold text-gray-900">{{ $order['number'] }}</dd>
                            <dt class="text-gray-500 sm:text-right">{{ __('Date:') }}</dt>
                            <dd class="text-gray-900">{{ $order['date'] }}</dd>
                            <dt class="text-gray-500 sm:text-right">{{ __('Order Status:') }}</dt>
                            <dd class="text-gray-900">{{ $order['status'] }}</dd>
                            <dt class="text-gray-500 sm:text-right">{{ __('Payment:') }}</dt>
                            <dd class="text-gray-900">{{ $order['payment_status'] }}</dd>
                        </dl>
                    </div>
                </header>

                {{-- Customer & payment --}}
                <div class="grid grid-cols-1 gap-4 border-b border-gray-200 px-4 py-4 sm:grid-cols-2 sm:px-6 print:grid-cols-2 print:px-0">
                    <div>
                        <h3 class="mb-1.5 text-[11px] font-semibold tracking-wider text-gray-500 uppercase">{{ __('Bill / Ship To') }}</h3>
                        <p class="text-sm font-semibold text-gray-900">{{ $order['customer_name'] }}</p>
                        <p class="mt-0.5">{{ $order['address'] }}</p>
                        <p class="mt-0.5">{{ __('Phone') }}: <span class="text-gray-900">{{ $order['customer_phone'] }}</span></p>
                        @if ($order['customer_email'])
                            <p class="mt-0.5">{{ __('Email') }}: <span class="text-gray-900">{{ $order['customer_email'] }}</span></p>
                        @endif
                    </div>
                    <div class="sm:text-right print:text-right">
                        <h3 class="mb-1.5 text-[11px] font-semibold tracking-wider text-gray-500 uppercase">{{ __('Payment Info') }}</h3>
                        <p>{{ __('Method') }}: <span class="font-semibold text-gray-900">{{ $order['payment_method'] }}</span></p>
                        @if ($order['shipping_method'])
                            <p class="mt-0.5">{{ __('Delivery') }}: <span class="text-gray-900">{{ $order['shipping_method'] }}</span></p>
                        @endif
                    </div>
                </div>

                {{-- Line items --}}
                <div class="overflow-x-auto px-4 py-4 sm:px-6 print:overflow-visible print:px-0">
                    <table class="w-full min-w-[520px] border-collapse text-left print:min-w-0">
                        <thead>
                            <tr class="bg-gray-900 text-[11px] tracking-wider text-white uppercase">
                                <th scope="col" class="w-10 px-2.5 py-2 text-center font-semibold">{{ __('SL') }}</th>
                                <th scope="col" class="px-2.5 py-2 font-semibold">{{ __('Item Description') }}</th>
                                <th scope="col" class="w-14 px-2.5 py-2 text-center font-semibold">{{ __('Qty') }}</th>
                                <th scope="col" class="w-28 px-2.5 py-2 text-right font-semibold">{{ __('Unit Price') }}</th>
                                <th scope="col" class="w-28 px-2.5 py-2 text-right font-semibold">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr class="border-b border-gray-200 align-top even:bg-gray-50">
                                    <td class="px-2.5 py-2.5 text-center text-gray-500">{{ \App\Support\Localization::digits($loop->iteration) }}</td>
                                    <td class="px-2.5 py-2.5">
                                        <span class="font-medium text-gray-900">{{ $item['name'] }}</span>
                                        @if ($item['sku'] || $item['warranty'])
                                            <span class="mt-0.5 block text-[11px] text-gray-500">{{ implode(' · ', array_filter([
                                                $item['sku'] ? __('SKU').": {$item['sku']}" : null,
                                                $item['warranty'] ? __('Warranty').": {$item['warranty']}" : null,
                                            ])) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-2.5 py-2.5 text-center text-gray-900">{{ \App\Support\Localization::digits($item['quantity']) }}</td>
                                    <td class="px-2.5 py-2.5 text-right whitespace-nowrap text-gray-900">{{ $item['unit_price'] }}</td>
                                    <td class="px-2.5 py-2.5 text-right font-semibold whitespace-nowrap text-gray-900">{{ $item['line_total'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Totals --}}
                <div class="flex flex-col gap-4 px-4 pb-5 sm:flex-row sm:justify-between sm:px-6 print:flex-row print:justify-between print:px-0">
                    <div class="sm:max-w-xs">
                        @if ($order['note'])
                            <h3 class="mb-1 text-[11px] font-semibold tracking-wider text-gray-500 uppercase">{{ __('Order Note') }}</h3>
                            <p class="text-gray-700">{{ $order['note'] }}</p>
                        @endif
                    </div>

                    <dl class="w-full space-y-1.5 sm:w-64 print:w-64">
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-600">{{ __('Subtotal') }}</dt>
                            <dd class="font-medium text-gray-900">{{ $totals['subtotal'] }}</dd>
                        </div>
                        @if ($totals['discount'])
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-600">{{ $totals['coupon_code'] ? __('Discount (:code)', ['code' => $totals['coupon_code']]) : __('Discount') }}</dt>
                                <dd class="font-medium text-savings">{{ $totals['discount'] }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-600">{{ __('Delivery Charge') }}</dt>
                            <dd class="font-medium text-gray-900">{{ $totals['shipping'] }}</dd>
                        </div>
                        @if ($totals['tax'])
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-600">{{ __('VAT / Tax') }}</dt>
                                <dd class="font-medium text-gray-900">{{ $totals['tax'] }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between gap-3 border-t-2 border-gray-900 pt-2 text-sm">
                            <dt class="font-bold text-gray-900">{{ __('Grand Total') }}</dt>
                            <dd class="font-bold text-gray-900">{{ $totals['grand_total'] }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-600">{{ __('Paid') }}</dt>
                            <dd class="font-medium text-gray-900">{{ $totals['paid'] }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 rounded bg-price/10 px-2 py-1.5 text-sm">
                            <dt class="font-bold text-gray-900">{{ __('Amount Due') }}</dt>
                            <dd class="font-bold text-price">{{ $totals['due'] }}</dd>
                        </div>
                    </dl>
                </div>

                <footer class="border-t border-dashed border-gray-300 px-4 py-4 text-center text-[11px] text-gray-500 sm:px-6 print:px-0">
                    <p class="font-medium text-gray-700">{{ __('Thank you for shopping with :name.', ['name' => $site['name']]) }}</p>
                    <p class="mt-0.5">{{ __('This is a computer-generated invoice and does not require a signature.') }}</p>
                </footer>
            </article>

            <div class="mt-5 flex flex-col items-center justify-center gap-2 sm:flex-row print:hidden">
                <button type="button" data-print-invoice class="inline-flex h-10 cursor-pointer items-center gap-2 rounded-md bg-button px-5 text-sm font-semibold text-button-text transition hover:bg-button-hover">
                    <x-store.icon name="printer" class="size-4" /> {{ __('Print / Download Invoice') }}
                </button>
                <a href="{{ route('store.home') }}" class="inline-flex h-10 items-center gap-2 rounded-md bg-button px-5 text-sm font-semibold text-button-text transition hover:bg-button-hover">
                    <x-store.icon name="arrow-left" class="size-4" /> {{ __('Continue Shopping') }}
                </a>
                @if ($contact['hotline_tel'])
                    <a href="tel:{{ $contact['hotline_tel'] }}" class="inline-flex h-10 items-center gap-2 rounded-md border border-gray-300 bg-white px-5 text-sm font-medium text-gray-800 transition hover:border-accent hover:text-accent">
                        <x-store.icon name="phone" class="size-4" /> {{ $contact['hotline'] }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-layouts.store>
