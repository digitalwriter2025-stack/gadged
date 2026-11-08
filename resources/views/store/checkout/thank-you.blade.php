<x-layouts.store :title="$seo['title']" :description="$seo['description']">
    <x-slot:head>
        <meta name="robots" content="noindex, nofollow">
    </x-slot:head>

    <div class="bg-surface py-6 lg:py-10" data-confetti-on-load>
        <div class="mx-auto max-w-2xl px-4">
            <section class="rounded-md border border-gray-200 bg-white px-4 py-8 text-center sm:px-8" aria-labelledby="thank-you-heading">
                <span class="mx-auto grid size-16 place-items-center rounded-full bg-savings/10 text-savings">
                    <x-store.icon name="check" class="size-8" />
                </span>
                <h1 id="thank-you-heading" class="mt-4 text-lg font-bold text-gray-900 sm:text-xl">Thank you for your order!</h1>
                <p class="mt-2 text-sm text-gray-600">Your order has been placed successfully. Our team will call you at <span class="font-semibold text-gray-900">{{ $order['customer_phone'] }}</span> to confirm it.</p>

                <p class="mt-5 inline-flex flex-wrap items-center justify-center gap-x-2 gap-y-1 rounded-md border border-dashed border-builder/40 bg-builder/5 px-4 py-2.5 text-sm">
                    <span class="text-gray-600">Order Number:</span>
                    <span class="font-bold tracking-wide text-builder">{{ $order['number'] }}</span>
                </p>
                <p class="mt-2 text-[11px] text-gray-500">Placed on {{ $order['date'] }}</p>
            </section>

            <section class="mt-4 rounded-md border border-gray-200 bg-white" aria-labelledby="order-items-heading">
                <h2 id="order-items-heading" class="border-b border-gray-200 px-4 py-3 text-sm font-semibold text-gray-900">Order Details</h2>

                <ul class="divide-y divide-gray-100 text-xs">
                    @foreach ($items as $item)
                        <li class="flex items-start justify-between gap-3 px-4 py-3">
                            <span class="min-w-0 text-gray-800">
                                {{ $item['name'] }}
                                <span class="mt-0.5 block text-[11px] text-gray-500">{{ $item['quantity'] }} × {{ $item['unit_price'] }}</span>
                            </span>
                            <span class="shrink-0 font-semibold text-gray-900">{{ $item['line_total'] }}</span>
                        </li>
                    @endforeach
                </ul>

                <dl class="space-y-2 border-t border-gray-200 px-4 py-3 text-xs">
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-600">Subtotal</dt>
                        <dd class="font-medium text-gray-900">{{ $totals['subtotal'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-600">Delivery Charge</dt>
                        <dd class="font-medium text-gray-900">{{ $totals['shipping'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 border-t border-dashed border-gray-200 pt-2 text-sm">
                        <dt class="font-semibold text-gray-900">Total</dt>
                        <dd class="font-bold text-price">{{ $totals['grand_total'] }}</dd>
                    </div>
                </dl>

                <dl class="grid grid-cols-1 gap-3 border-t border-gray-200 px-4 py-3 text-xs sm:grid-cols-2">
                    <div>
                        <dt class="text-gray-500">Deliver To</dt>
                        <dd class="mt-0.5 font-medium text-gray-900">{{ $order['customer_name'] }}</dd>
                        <dd class="text-gray-700">{{ $order['address'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Payment Method</dt>
                        <dd class="mt-0.5 font-medium text-gray-900">{{ $order['payment_method'] }}</dd>
                    </div>
                    @if ($order['note'])
                        <div class="sm:col-span-2">
                            <dt class="text-gray-500">Order Note</dt>
                            <dd class="mt-0.5 text-gray-700">{{ $order['note'] }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            <div class="mt-5 flex flex-col items-center justify-center gap-2 sm:flex-row">
                <a href="{{ route('store.home') }}" class="inline-flex h-10 items-center gap-2 rounded-md bg-builder px-5 text-sm font-semibold text-white transition hover:bg-black">
                    <x-store.icon name="arrow-left" class="size-4" /> Continue Shopping
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
