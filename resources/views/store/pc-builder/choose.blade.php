<x-layouts.store :title="$seo['title']" :description="$seo['description']">
    <x-slot:head>
        <meta name="robots" content="noindex,follow">
    </x-slot:head>

    <x-store.breadcrumbs :items="$breadcrumbs" />

    <div class="bg-surface py-4 lg:py-6">
        <div class="mx-auto max-w-[58rem] px-3 sm:px-4">
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-md border border-gray-200 bg-white px-4 py-3">
                <div>
                    <h1 class="text-base font-bold text-gray-900 lg:text-lg">{{ __('Choose :component', ['component' => $part['label']]) }}</h1>
                    <p class="text-xs text-gray-500">{{ trans_choice(':count product available|:count products available', $products->total(), ['count' => \App\Support\Localization::digits(number_format($products->total()))]) }}</p>
                </div>

                <form method="GET" class="flex items-center gap-2 text-xs text-gray-600">
                    <label for="pc-sort">{{ __('Sort By:') }}</label>
                    <select id="pc-sort" name="sort" data-autosubmit class="h-9 rounded-md border border-gray-300 bg-white px-2 text-xs text-gray-900">
                        @foreach ($sorts as $value => $label)
                            <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <noscript><button type="submit" class="h-9 rounded-md bg-button px-3 text-button-text hover:bg-button-hover">{{ __('Apply') }}</button></noscript>
                </form>
            </div>

            @error('product_id')
                <p class="mt-3 rounded-md bg-red-50 px-3 py-2 text-xs text-price" role="alert">{{ $message }}</p>
            @enderror

            @if ($products->isEmpty())
                <div class="mt-3 rounded-md border border-gray-200 bg-white px-4 py-12 text-center">
                    <p class="text-sm font-medium text-gray-900">{{ __('No :component products are available right now.', ['component' => $part['label']]) }}</p>
                    <a href="{{ route('store.pc-builder.show') }}" class="mt-3 inline-block text-xs font-semibold text-builder hover:underline">{{ __('Back to PC Builder') }}</a>
                </div>
            @else
                <ul class="mt-3 space-y-3">
                    @foreach ($products as $product)
                        <li class="flex flex-col gap-3 rounded-md border border-gray-200 bg-white p-3 sm:flex-row sm:items-center sm:gap-5 sm:p-4">
                            <a href="{{ $product['url'] }}" class="flex min-w-0 flex-1 items-start gap-3 sm:gap-4">
                                <img src="{{ $product['image'] }}" alt="" width="96" height="96" loading="lazy" class="size-20 shrink-0 object-contain sm:size-24">
                                <span class="min-w-0">
                                    <span class="line-clamp-2 text-sm font-semibold text-gray-900 transition hover:text-accent">{{ $product['name'] }}</span>
                                    @if ($product['features'] !== [])
                                        <ul class="mt-1.5 hidden list-disc space-y-0.5 pl-4 text-xs text-gray-600 sm:block">
                                            @foreach ($product['features'] as $feature)
                                                <li>{{ $feature }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </span>
                            </a>

                            <div class="flex items-center justify-between gap-3 border-t border-gray-100 pt-3 sm:w-36 sm:flex-col sm:items-end sm:border-0 sm:pt-0">
                                <div class="text-right">
                                    <p class="text-base font-bold text-price">{{ $product['price'] }}</p>
                                    @if ($product['regular_price'])
                                        <p class="text-xs text-gray-400 line-through">{{ $product['regular_price'] }}</p>
                                    @endif
                                </div>

                                @if ($selectedId === $product['id'])
                                    <span class="inline-flex items-center gap-1 rounded bg-green-50 px-4 py-2 text-xs font-semibold text-savings">
                                        <x-store.icon name="check" class="size-4" /> {{ __('Selected') }}
                                    </span>
                                @elseif ($product['purchasable'])
                                    <form method="POST" action="{{ route('store.pc-builder.store', $part['key']) }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                                        <button type="submit" class="cursor-pointer rounded bg-button px-5 py-2 text-xs font-semibold text-button-text transition hover:bg-button-hover">{{ __('Add') }}</button>
                                    </form>
                                @else
                                    <span class="rounded bg-gray-100 px-3 py-2 text-xs font-medium text-gray-500">{{ $product['stock_label'] }}</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-4">
                    {{ $products->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.store>
