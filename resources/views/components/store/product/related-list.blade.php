@props(['products'])

<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-md border border-gray-200 bg-white']) }} aria-labelledby="related-products-title">
    <div class="flex items-center gap-2 border-b border-gray-100 bg-linear-to-r from-accent/[0.06] to-transparent px-4 py-3">
        <span class="h-4 w-1 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
        <h2 id="related-products-title" class="flex-1 text-sm font-bold text-gray-900">{{ __('Related Products') }}</h2>
        @if ($products !== [])
            <span class="rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-gray-500 ring-1 ring-gray-200">{{ \App\Support\Localization::digits(count($products)) }}</span>
        @endif
    </div>

    @if ($products === [])
        <div class="flex flex-col items-center gap-2 px-4 py-8 text-center">
            <span class="grid size-10 place-items-center rounded-full bg-surface text-gray-400">
                <x-store.icon name="package" class="size-5" />
            </span>
            <p class="text-xs text-gray-500">{{ __('No related products yet.') }}</p>
        </div>
    @else
        <ul class="scrollbar-none flex snap-x gap-3 overflow-x-auto p-3 lg:block lg:max-h-[calc(100dvh-11rem)] lg:divide-y lg:divide-gray-100 lg:overflow-x-visible lg:overflow-y-auto lg:p-0 lg:pb-6 lg:[mask-image:linear-gradient(to_bottom,#000_calc(100%-2.5rem),transparent)]">
            @foreach ($products as $related)
                <li class="w-60 shrink-0 snap-start lg:w-auto">
                    <a href="{{ $related['url'] }}" class="group relative flex h-full items-center gap-3 rounded-md border border-gray-200 p-2.5 transition duration-300 hover:border-accent/30 hover:shadow-sm lg:rounded-none lg:border-0 lg:px-4 lg:py-3 lg:hover:bg-accent/[0.03] lg:hover:shadow-none">
                        <span class="absolute inset-y-3 left-0 hidden w-0.5 scale-y-0 rounded-full bg-accent transition-transform duration-300 ease-out-expo group-hover:scale-y-100 lg:block" aria-hidden="true"></span>
                        <span class="grid size-16 shrink-0 place-items-center overflow-hidden rounded-md border border-gray-100 bg-white p-1 transition group-hover:border-accent/20">
                            <img src="{{ $related['image'] }}" alt="" width="64" height="64" loading="lazy" class="size-full object-contain transition duration-500 ease-out-expo group-hover:scale-110">
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="line-clamp-2 text-xs leading-[1.125rem] font-medium text-gray-800 transition group-hover:text-accent">{{ $related['name'] }}</span>
                            <span class="mt-1 flex flex-wrap items-baseline gap-x-1.5">
                                <span class="text-sm font-bold text-price">{{ $related['price'] }}</span>
                                @if ($related['regular_price'])
                                    <del class="text-[11px] text-gray-400">{{ $related['regular_price'] }}</del>
                                @endif
                            </span>
                            @if ($related['savings'])
                                <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-savings/10 px-1.5 py-0.5 text-[10px] leading-none font-semibold text-savings">
                                    <x-store.icon name="tag" class="size-2.5" /> {{ __('Save: :amount', ['amount' => $related['savings']]) }}
                                </span>
                            @endif
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</section>
