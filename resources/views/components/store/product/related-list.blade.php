@props(['products'])

<section {{ $attributes->merge(['class' => 'rounded-md border border-gray-200 bg-white']) }} aria-labelledby="related-products-title">
    <h2 id="related-products-title" class="border-b border-gray-200 px-4 py-3 text-sm font-semibold text-gray-900">Related Products</h2>

    @if ($products === [])
        <p class="px-4 py-6 text-center text-xs text-gray-500">No related products yet.</p>
    @else
        <ul class="scrollbar-none flex snap-x gap-3 overflow-x-auto p-3 lg:block lg:max-h-[26rem] lg:space-y-1 lg:overflow-x-visible lg:overflow-y-auto lg:p-2">
            @foreach ($products as $related)
                <li class="w-56 shrink-0 snap-start lg:w-auto">
                    <a href="{{ $related['url'] }}" class="group flex gap-3 rounded-md border border-gray-100 p-2 transition hover:bg-surface lg:border-transparent">
                        <span class="grid size-14 shrink-0 place-items-center overflow-hidden rounded bg-white">
                            <img src="{{ $related['image'] }}" alt="" width="56" height="56" loading="lazy" class="size-full object-contain transition duration-500 ease-out-expo group-hover:scale-110">
                        </span>
                        <span class="min-w-0">
                            <span class="line-clamp-2 text-xs leading-4 font-medium text-gray-800 transition group-hover:text-accent">{{ $related['name'] }}</span>
                            <span class="mt-1 flex flex-wrap items-baseline gap-x-2">
                                <span class="text-[13px] font-bold text-price">{{ $related['price'] }}</span>
                                @if ($related['regular_price'])
                                    <del class="text-[11px] text-gray-400">{{ $related['regular_price'] }}</del>
                                @endif
                            </span>
                            @if ($related['savings'])
                                <span class="mt-0.5 block text-[11px] font-medium text-savings">Save: {{ $related['savings'] }}</span>
                            @endif
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</section>
