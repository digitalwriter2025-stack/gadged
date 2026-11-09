@php
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => $category['heading'],
        'itemListElement' => collect($products->items())->values()->map(fn (array $product, int $index): array => [
            '@type' => 'ListItem',
            'position' => $products->firstItem() + $index,
            'url' => $product['url'],
            'name' => $product['name'],
        ])->all(),
    ];
@endphp

<x-layouts.store :title="$seo['title']" :description="$seo['description']">
    <x-slot:head>
        <link rel="canonical" href="{{ $seo['canonical'] }}">
        @if ($seo['noindex'])
            <meta name="robots" content="noindex,follow">
        @endif
        <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    </x-slot:head>

    <x-store.breadcrumbs :items="$breadcrumbs" container="max-w-hero" />

    <section class="border-b border-gray-200 bg-white">
        <div class="mx-auto max-w-hero px-4 py-5 lg:py-7">
            <h1 class="text-lg font-bold text-gray-900 lg:text-2xl">{{ $category['heading'] }}</h1>
            <p class="mt-2 line-clamp-3 text-xs leading-5 text-gray-600 lg:line-clamp-none lg:text-[13px] lg:leading-6" data-category-intro>{{ $category['intro'] }}</p>

            @if ($subcategories !== [])
                <ul class="mt-4 flex flex-wrap items-center gap-y-2 text-[13px] lg:text-sm" aria-label="{{ __('Sub categories') }}">
                    @foreach ($subcategories as $subcategory)
                        <li class="flex items-center">
                            <a href="{{ $subcategory['url'] }}" class="text-builder transition hover:text-accent hover:underline">{{ $subcategory['name'] }}</a>
                            @unless ($loop->last)
                                <span class="mx-3 h-3.5 w-px bg-gray-300" aria-hidden="true"></span>
                            @endunless
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>

    <div class="bg-surface" data-category-page>
        <form method="GET" action="{{ $category['url'] }}" data-listing-form class="group/listing mx-auto grid max-w-hero grid-cols-1 gap-4 px-4 py-4 lg:grid-cols-[16rem_minmax(0,1fr)] lg:gap-5 lg:py-6">
            <x-store.listing.filter-sidebar
                :groups="$filterGroups"
                :price="$price"
                :filters="$filters"
                :clear-url="$category['url']"
                :active-count="$activeFilterCount"
                class="lg:sticky lg:top-28 lg:self-start" />
            <div class="filter-backdrop lg:hidden" data-filter-close aria-hidden="true"></div>

            <div class="min-w-0 space-y-3 lg:space-y-4">
                <div class="flex flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-white p-3 lg:gap-3 lg:p-4">
                    <button type="button" data-filter-open aria-controls="listing-filters" aria-expanded="false"
                        @class([
                            'flex h-10 cursor-pointer items-center gap-2 rounded-lg border px-3.5 text-sm font-medium transition active:scale-[0.98] lg:hidden',
                            'border-gray-300 text-gray-700 hover:border-gray-400' => $activeFilterCount === 0,
                            'border-builder bg-builder text-white' => $activeFilterCount > 0,
                        ])>
                        <x-store.icon name="filter" class="size-4" />
                        {{ __('Filter') }}
                        @if ($activeFilterCount > 0)
                            <span class="grid h-5 min-w-5 place-items-center rounded-full bg-white px-1.5 text-[10px] font-semibold text-builder tabular-nums">{{ \App\Support\Localization::digits($activeFilterCount) }}</span>
                        @endif
                    </button>

                    <label class="order-last flex h-10 w-full items-center rounded-lg border border-gray-300 transition focus-within:border-builder focus-within:ring-2 focus-within:ring-builder/15 sm:order-none sm:w-auto sm:flex-1 lg:h-11">
                        <span class="sr-only">{{ __('Search in :name', ['name' => $category['name']]) }}</span>
                        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="{{ __('Search by product name...') }}" maxlength="100"
                            class="h-full w-full bg-transparent px-4 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none">
                        <button type="submit" class="grid h-full w-11 shrink-0 cursor-pointer place-items-center text-gray-400 transition hover:text-builder" aria-label="{{ __('Search') }}">
                            <x-store.icon name="search" class="size-5" />
                        </button>
                    </label>

                    <label class="relative ml-auto sm:ml-0">
                        <span class="sr-only">{{ __('Sort products') }}</span>
                        <select name="sort" data-filter-input data-default="{{ array_key_first($sorts) }}"
                            class="h-10 cursor-pointer appearance-none rounded-lg border border-gray-300 bg-white py-0 pr-10 pl-3 text-sm text-gray-900 focus:border-builder focus:ring-2 focus:ring-builder/15 focus:outline-none sm:w-44 lg:h-11">
                            @foreach ($sorts as $value => $label)
                                <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-store.icon name="chevron-down" class="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-gray-600" />
                    </label>
                </div>

                <x-store.listing.active-filters :groups="$filterGroups" :filters="$filters" :price="$price" :clear-url="$category['url']" />

                <p class="px-1 text-xs text-gray-500" aria-live="polite">
                    @if ($products->total() > 0)
                        {{ __('Showing :from–:to of :total products', ['from' => \App\Support\Localization::digits($products->firstItem()), 'to' => \App\Support\Localization::digits($products->lastItem()), 'total' => \App\Support\Localization::digits(number_format($products->total()))]) }}
                    @endif
                </p>

                @if ($products->isEmpty() && $activeFilterCount === 0 && isset($emptyState))
                    <div class="rounded-lg border border-gray-200 bg-white px-4 py-16 text-center" data-listing-empty>
                        <x-store.icon name="package" class="mx-auto size-10 text-gray-300" />
                        <p class="mt-3 text-sm font-semibold text-gray-800">{{ $emptyState['title'] }}</p>
                        <p class="mx-auto mt-1 max-w-sm text-xs leading-5 text-gray-500">{{ $emptyState['text'] }}</p>
                        <a href="{{ $emptyState['action']['url'] ?? route('store.home') }}" class="mt-4 inline-flex h-9 items-center rounded-md bg-button px-4 text-xs font-semibold text-button-text hover:bg-button-hover">{{ $emptyState['action']['label'] ?? __('Back to Home') }}</a>
                    </div>
                @elseif ($products->isEmpty())
                    <div class="rounded-lg border border-gray-200 bg-white px-4 py-16 text-center">
                        <x-store.icon name="search" class="mx-auto size-10 text-gray-300" />
                        <p class="mt-3 text-sm font-semibold text-gray-800">{{ __('No products found') }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Try removing some filters or searching with a different name.') }}</p>
                        <a href="{{ $category['url'] }}" class="mt-4 inline-flex h-9 items-center rounded-md bg-button px-4 text-xs font-semibold text-button-text hover:bg-button-hover">{{ __('Clear all filters') }}</a>
                    </div>
                @else
                    <ul class="grid grid-cols-2 gap-2.5 transition-opacity duration-200 group-aria-busy/listing:pointer-events-none group-aria-busy/listing:opacity-50 sm:gap-3 md:grid-cols-3 xl:grid-cols-4 xl:gap-4">
                        @foreach ($products as $product)
                            <li><x-store.listing-card :product="$product" /></li>
                        @endforeach
                    </ul>

                    @if ($products->hasPages())
                        <div class="rounded-lg border border-gray-200 bg-white px-3 py-3">
                            {{ $products->onEachSide(1)->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </form>
    </div>
</x-layouts.store>
