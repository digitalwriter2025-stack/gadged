@php
    $maxQuantity = (int) config('shop.cart.max_quantity_per_line');
    $linkClass = 'text-accent underline underline-offset-2 hover:text-accent-dark';
    $digits = fn (int|string $value): string => \App\Support\Localization::digits($value);
    $tabs = array_filter([
        'specification' => __('Specification'),
        'description' => __('Description'),
        'faqs' => $faqs !== [] ? __('FAQs') : null,
        'reviews' => __('Reviews (:count)', ['count' => $digits($product['rating_count'])]),
    ]);
    $chips = array_filter([
        'stock' => [__('Stock'), $product['stock_label']],
        'pid' => [__('PID'), $product['sku']],
        'brand' => [__('Brand'), $product['brand']],
        'model' => [__('Model'), $product['model']],
        'warranty' => [__('Warranty'), $product['warranty']],
    ], fn (array $chip): bool => filled($chip[1]));
    $brandLink = $product['brand_url'] ? '<a href="'.e($product['brand_url']).'" class="'.$linkClass.'">'.e($product['brand']).'</a>' : e($product['brand']);
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product['name'],
        'sku' => $product['sku'],
        'image' => array_column($images, 'large'),
        'description' => $seo['description'],
        'brand' => $product['brand'] ? ['@type' => 'Brand', 'name' => $product['brand']] : null,
        'offers' => [
            '@type' => 'Offer',
            'url' => $product['url'],
            'priceCurrency' => config('shop.currency.code'),
            'price' => $pricing['amount'],
            'availability' => $product['in_stock'] ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        ],
    ];
    $faqData = $faqs === [] ? null : [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(fn (array $faq): array => [
            '@type' => 'Question',
            'name' => $faq['question'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
        ], $faqs),
    ];
@endphp

<x-layouts.store :title="$seo['title']" :description="$seo['description']">
    <x-slot:head>
        <link rel="canonical" href="{{ $product['url'] }}">
        <script type="application/ld+json">{!! json_encode(array_filter($structuredData), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
        @if ($faqData)
            <script type="application/ld+json">{!! json_encode($faqData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
        @endif
    </x-slot:head>

    <x-store.breadcrumbs :items="$breadcrumbs" />

    <div class="bg-surface" data-product-page>
        <div class="mx-auto grid max-w-store grid-cols-1 gap-4 px-4 py-4 lg:grid-cols-[15rem_minmax(0,1fr)] lg:py-5">
            <aside class="order-last min-w-0 lg:order-first">
                <x-store.product.related-list :products="$relatedProducts" class="lg:sticky lg:top-28" />
            </aside>

            <div class="min-w-0 space-y-4">
                <section class="grid grid-cols-1 gap-5 rounded-md border border-gray-200 bg-white p-4 md:grid-cols-[minmax(0,5fr)_minmax(0,6fr)] lg:p-5" aria-label="{{ __('Product overview') }}">
                    <x-store.product.gallery :images="$images" :savings="$pricing['savings']" />

                    <div class="min-w-0">
                        <h1 class="text-base leading-snug font-semibold text-gray-900 lg:text-lg">{{ $product['name'] }}</h1>

                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                            <x-store.product.rating-stars :rating="$product['rating']" />
                            <a href="#reviews" data-product-tab-link class="transition hover:text-accent">{{ trans_choice(':count Review|:count Reviews', $product['rating_count'], ['count' => $digits($product['rating_count'])]) }}</a>
                            <button type="button" data-modal-open="disclaimer-modal" class="inline-flex cursor-pointer items-center gap-1 rounded border border-builder/20 bg-builder/5 px-2 py-0.5 text-[11px] font-medium text-builder transition hover:bg-builder/10">
                                <x-store.icon name="info" class="size-3" /> {{ __('Disclaimer') }}
                            </button>
                            <button type="button" data-modal-open="suggestion-modal" class="inline-flex cursor-pointer items-center gap-1 rounded border border-savings/20 bg-savings/5 px-2 py-0.5 text-[11px] font-medium text-savings transition hover:bg-savings/10">
                                <x-store.icon name="chat" class="size-3" /> {{ __('Suggestion') }}
                            </button>
                        </div>

                        <ul class="mt-3 flex flex-wrap gap-1.5 text-[11px]">
                            @foreach ($chips as $key => [$label, $value])
                                <li @class([
                                    'rounded border px-2 py-1',
                                    'border-savings/30 bg-savings/5 text-savings' => $key === 'stock' && $product['in_stock'],
                                    'border-price/30 bg-price/5 text-price' => $key === 'stock' && ! $product['in_stock'],
                                    'border-gray-200 bg-gray-50 text-gray-500' => $key !== 'stock',
                                ])>
                                    {{ $label }} : <span class="font-medium text-gray-900">{{ $value }}</span>
                                </li>
                            @endforeach
                        </ul>

                        @if ($keyFeatures !== [])
                            <div class="mt-4">
                                <h2 class="sr-only">{{ __('Key Features') }}</h2>
                                <ul class="list-disc space-y-1 pl-4 text-xs text-gray-600 marker:text-gray-400">
                                    @foreach ($keyFeatures as $feature)
                                        <li>{{ $feature }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @elseif ($product['short_description'])
                            <p class="mt-4 text-xs leading-5 text-gray-600">{{ $product['short_description'] }}</p>
                        @endif

                        @if ($product['offer'])
                            <p class="mt-3 inline-flex items-center gap-1.5 rounded border border-savings/30 bg-savings/5 px-2.5 py-1.5 text-[11px] font-semibold text-savings">
                                <x-store.icon name="tag" class="size-3.5" /> {{ $product['offer'] }}
                            </p>
                        @endif

                        @if ($combo)
                            <section class="mt-4 rounded-md border border-gray-200" aria-labelledby="combo-heading" data-combo-contents>
                                <h2 id="combo-heading" class="flex items-center gap-1.5 border-b border-gray-200 bg-surface px-3 py-2 text-xs font-bold text-gray-900">
                                    <x-store.icon name="package" class="size-4 text-accent" /> {{ __("What's in this combo") }}
                                </h2>
                                <ul class="divide-y divide-gray-100">
                                    @foreach ($combo['items'] as $item)
                                        <li class="flex items-center gap-3 px-3 py-2">
                                            <img src="{{ $item['image'] }}" alt="" width="40" height="40" loading="lazy" decoding="async" class="size-10 shrink-0 rounded border border-gray-100 object-contain">
                                            <span class="min-w-0 flex-1 text-xs leading-5 text-gray-800">
                                                @if ($item['url'])
                                                    <a href="{{ $item['url'] }}" class="hover:text-accent hover:underline">{{ $item['name'] }}</a>
                                                @else
                                                    {{ $item['name'] }}
                                                @endif
                                            </span>
                                            <span class="shrink-0 text-xs font-semibold text-gray-600">× {{ $digits($item['quantity']) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                @if ($combo['savings'])
                                    <p class="border-t border-gray-200 px-3 py-2 text-[11px] text-gray-600">
                                        {{ __('Bought separately:') }} <s>{{ $combo['separate_total'] }}</s>
                                        <span class="ml-1 font-semibold text-savings">{{ __('You save :amount', ['amount' => $combo['savings']]) }}</span>
                                    </p>
                                @endif
                            </section>
                        @endif

                        <div class="mt-4 grid gap-2 {{ $pricing['emi_monthly'] ? 'sm:grid-cols-2' : '' }}">
                            <div class="rounded-md border border-gray-200 p-3">
                                <p class="text-[11px] text-gray-500">{{ $pricing['regular_price'] ? __('Discount Price') : __('Price') }}</p>
                                <p class="mt-1 flex flex-wrap items-baseline gap-x-2">
                                    <span class="text-xl font-bold text-price" data-product-price>{{ $pricing['price'] }}</span>
                                    @if ($pricing['regular_price'])
                                        <del class="text-xs text-gray-400">{{ $pricing['regular_price'] }}</del>
                                    @endif
                                </p>
                                <button type="button" data-modal-open="payment-modal" class="mt-1 cursor-pointer text-[11px] text-builder hover:underline">+ {{ __('Available Payment Method') }}</button>
                            </div>

                            @if ($pricing['emi_monthly'])
                                <div class="rounded-md border border-gray-200 p-3">
                                    <p class="text-[11px] text-gray-500">{{ __('EMI Start From') }}*</p>
                                    <p class="mt-1 text-xl font-bold text-builder">{{ $pricing['emi_monthly'] }}</p>
                                    <button type="button" data-modal-open="emi-modal" class="mt-1 inline-flex cursor-pointer items-center gap-1 text-[11px] text-builder hover:underline">
                                        <x-store.icon name="bank" class="size-3" /> {{ __('View Banks EMI Plans') }}
                                    </button>
                                </div>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('store.cart.store') }}" class="mt-4 flex flex-wrap items-center gap-2" data-add-to-cart data-product-id="{{ $product['id'] }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                            <div class="flex h-10 items-center rounded-md border border-gray-300" data-quantity>
                                <button type="button" data-quantity-step="-1" class="grid h-full w-9 cursor-pointer place-items-center text-gray-600 transition hover:text-accent disabled:cursor-not-allowed disabled:opacity-40" aria-label="{{ __('Decrease quantity') }}">
                                    <x-store.icon name="minus" class="size-4" />
                                </button>
                                <label for="quantity" class="sr-only">{{ __('Quantity') }}</label>
                                <input id="quantity" name="quantity" type="number" inputmode="numeric" value="1" min="1" max="{{ $maxQuantity }}" class="h-full w-11 [appearance:textfield] border-x border-gray-300 text-center text-sm font-medium focus:outline-none [&::-webkit-inner-spin-button]:appearance-none">
                                <button type="button" data-quantity-step="1" class="grid h-full w-9 cursor-pointer place-items-center text-gray-600 transition hover:text-accent disabled:cursor-not-allowed disabled:opacity-40" aria-label="{{ __('Increase quantity') }}">
                                    <x-store.icon name="plus" class="size-4" />
                                </button>
                            </div>

                            <button type="submit" data-ripple @disabled(! $product['purchasable']) class="relative flex h-10 min-w-36 flex-1 cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-md bg-button px-5 text-sm font-semibold text-button-text transition hover:bg-button-hover active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-gray-400">
                                <x-store.icon name="cart" class="size-4" />
                                {{ $product['purchasable'] ? __('Add to Cart') : $product['stock_label'] }}
                            </button>

                            <button type="button" data-wishlist aria-pressed="false" data-analytics-item="{{ json_encode(array_filter(['item_id' => (string) $product['id'], 'item_name' => $product['name'], 'item_brand' => $product['brand'], 'item_category' => $product['category'], 'price' => (float) $pricing['amount']])) }}" class="flex h-10 cursor-pointer items-center gap-1.5 rounded-md border border-gray-300 bg-gray-50 px-3 text-xs font-medium text-gray-700 transition hover:border-accent hover:text-accent">
                                <x-store.icon name="heart" class="size-4" /> {{ __('Wishlist') }}
                            </button>
                            <button type="button" data-compare aria-pressed="false" class="flex h-10 cursor-pointer items-center gap-1.5 rounded-md border border-gray-300 bg-gray-50 px-3 text-xs font-medium text-gray-700 transition hover:border-accent hover:text-accent">
                                <x-store.icon name="compare" class="size-4" /> {{ __('Compare') }}
                            </button>
                        </form>
                    </div>
                </section>

                <nav class="sticky top-0 z-20 rounded-md border border-gray-200 bg-white/95 backdrop-blur transition-[top] duration-300" data-product-tabs aria-label="{{ __('Product sections') }}">
                    <ul class="scrollbar-none flex gap-1 overflow-x-auto px-2">
                        @foreach ($tabs as $id => $label)
                            <li>
                                <a href="#{{ $id }}" data-product-tab-link data-section="{{ $id }}" @class([
                                    'block border-b-2 px-3 py-3 text-xs font-medium whitespace-nowrap transition',
                                    'border-builder text-builder' => $loop->first,
                                    'border-transparent text-gray-600 hover:text-gray-900' => ! $loop->first,
                                ])>{{ $label }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                <section id="specification" class="scroll-mt-40 rounded-md border border-gray-200 bg-white p-4 lg:p-5" data-product-section>
                    <h2 class="mb-4 text-sm font-bold tracking-wide text-gray-900 uppercase">{{ __('Specifications') }}</h2>
                    <x-store.product.specification-table :groups="$specificationGroups" />
                </section>

                <section id="description" class="scroll-mt-40 rounded-md border border-gray-200 bg-white p-4 lg:p-5" data-product-section>
                    <h2 class="mb-3 text-sm font-bold tracking-wide text-gray-900 uppercase">{{ __('Description') }}</h2>
                    @if ($product['description'])
                        <div class="rich-text">{!! $product['description'] !!}</div>
                    @elseif ($product['short_description'])
                        <p class="text-[13px] leading-6 text-gray-700">{{ $product['short_description'] }}</p>
                    @else
                        <p class="text-[13px] text-gray-500">{{ __('Description will be added soon.') }}</p>
                    @endif
                </section>

                @if ($faqs !== [])
                    <section id="faqs" class="scroll-mt-40 rounded-md border border-gray-200 bg-white p-4 lg:p-5" data-product-section>
                        <h2 class="mb-3 text-sm font-bold text-gray-900">{{ __('FAQs') }}</h2>
                        <div class="overflow-hidden rounded-md border border-gray-200 divide-y divide-gray-200">
                            @foreach ($faqs as $faq)
                                <details class="group/faq">
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-3 py-3 text-xs font-medium text-gray-800 transition hover:bg-gray-50 sm:px-4 [&::-webkit-details-marker]:hidden">
                                        {{ $faq['question'] }}
                                        <x-store.icon name="chevron-down" class="size-4 text-gray-500 transition duration-200 group-open/faq:rotate-180" />
                                    </summary>
                                    <p class="px-3 pb-3 text-[13px] leading-6 whitespace-pre-line text-gray-600 sm:px-4">{{ $faq['answer'] }}</p>
                                </details>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section id="reviews" class="scroll-mt-40 rounded-md border border-gray-200 bg-white p-4 lg:p-5" data-product-section>
                    <h2 class="mb-3 text-sm font-bold tracking-wide text-gray-900 uppercase">{{ __('Reviews') }}</h2>
                    <x-store.product.reviews :reviews="$reviews" :rating="$product['rating']" :rating-count="$product['rating_count']" :action="$product['review_url']" :own-review-status="$ownReviewStatus" />
                </section>

                <section class="rounded-md border border-gray-200 bg-white p-4 lg:p-5">
                    <h2 class="text-sm font-semibold text-gray-900">{{ $seo['heading'] }}</h2>
                    <p class="mt-2 text-[13px] leading-6 text-gray-600">
                        {{ __('The latest price of :name in Bangladesh is :price.', ['name' => $product['name'], 'price' => $pricing['price']]) }}
                        @if ($product['brand'])
                            {!! $product['warranty']
                                ? __('This product is manufactured by :brand and comes with :warranty warranty.', ['brand' => $brandLink, 'warranty' => e($product['warranty'])])
                                : __('This product is manufactured by :brand.', ['brand' => $brandLink]) !!}
                        @elseif ($product['warranty'])
                            {{ __('It comes with :warranty warranty.', ['warranty' => $product['warranty']]) }}
                        @endif
                        {{ trans_choice('Based on :count review.|Based on :count reviews.', $product['rating_count'], ['count' => $digits($product['rating_count'])]) }}
                        @php($storeLink = '<a href="'.e(route('store.home')).'" class="'.$linkClass.'">'.e(config('app.name')).'</a>')
                        {!! $pricing['regular_price']
                            ? __(':store offers you :name for :price, while its regular price is :regular, so you save :savings.', ['store' => $storeLink, 'name' => e($product['name']), 'price' => e($pricing['price']), 'regular' => e($pricing['regular_price']), 'savings' => e($pricing['savings'])])
                            : __(':store offers you :name for :price.', ['store' => $storeLink, 'name' => e($product['name']), 'price' => e($pricing['price'])]) !!}
                        {{ __('The product is currently :stock.', ['stock' => Str::lower($product['stock_label'])]) }}
                        @if ($product['category_url'])
                            {!! __('Explore more :category at the best price in Bangladesh.', ['category' => '<a href="'.e($product['category_url']).'" class="'.$linkClass.'">'.e($product['category']).'</a>']) !!}
                        @endif
                    </p>
                </section>
            </div>
        </div>
    </div>

    <x-store.modal id="disclaimer-modal" :title="__('Disclaimer')">
        <p>{{ __('Product images, specifications and prices are for reference only and may change without prior notice. Please confirm the latest price, stock and warranty with our sales team before placing an order.') }}</p>
    </x-store.modal>

    <x-store.modal id="suggestion-modal" :title="__('Need a suggestion?')">
        <p>{{ __('Not sure this is the right product for you? Our experts are happy to help you choose.') }}</p>
        <div class="mt-4 grid gap-2 sm:grid-cols-2">
            @if ($contact['hotline_tel'])
                <a href="tel:{{ $contact['hotline_tel'] }}" class="flex h-10 items-center justify-center gap-2 rounded-md bg-button text-xs font-semibold text-button-text hover:bg-button-hover"><x-store.icon name="phone" class="size-4" /> {{ $contact['hotline'] }}</a>
            @endif
            @if ($contact['whatsapp'])
                <a href="https://wa.me/{{ $contact['whatsapp'] }}" target="_blank" rel="noopener" class="flex h-10 items-center justify-center gap-2 rounded-md bg-whatsapp text-xs font-semibold text-white hover:bg-[#1da851]"><x-store.icon name="whatsapp" class="size-4" /> WhatsApp</a>
            @endif
        </div>
    </x-store.modal>

    <x-store.modal id="payment-modal" :title="__('Available Payment Methods')">
        <ul class="grid grid-cols-2 gap-2">
            @foreach ($paymentMethods as $method)
                <li class="flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2.5 text-xs font-medium text-gray-800"><x-store.icon name="check" class="size-3.5 text-savings" /> {{ $method }}</li>
            @endforeach
        </ul>
    </x-store.modal>

    @if ($pricing['emi_plans'] !== [])
        <x-store.modal id="emi-modal" :title="__('EMI Plans')">
            <table class="w-full overflow-hidden rounded-md text-left text-xs">
                <thead class="bg-gray-100 text-gray-700">
                    <tr><th class="px-3 py-2 font-semibold">{{ __('Tenure') }}</th><th class="px-3 py-2 font-semibold">{{ __('Monthly Instalment') }}</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($pricing['emi_plans'] as $plan)
                        <tr><td class="px-3 py-2.5">{{ __(':count Months', ['count' => $digits($plan['months'])]) }}</td><td class="px-3 py-2.5 font-semibold text-builder">{{ $plan['monthly'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
            <p class="mt-3 text-[11px] text-gray-500">* {{ __('EMI is available on credit cards of partner banks. Bank charges may apply.') }}</p>
        </x-store.modal>
    @endif
</x-layouts.store>
