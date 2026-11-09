@props(['product'])

<article class="@container group relative flex h-full flex-col rounded-xl border border-white/60 bg-white/75 p-3 shadow-[0_1px_2px_rgba(15,23,42,0.04),inset_0_1px_0_rgba(255,255,255,0.8)] backdrop-blur-md transition duration-300 ease-out-expo hover:-translate-y-0.5 hover:border-builder/25 hover:bg-white/90 hover:shadow-[0_16px_32px_-12px_rgba(15,23,42,0.22)] lg:p-4" data-listing-card>
    <div class="relative">
        @if ($product['savings'])
            <span class="absolute top-1.5 left-1.5 z-10 rounded-full border border-white/40 bg-savings/85 px-2 py-1 text-[10px] leading-none font-semibold text-white shadow-sm backdrop-blur-sm lg:text-[11px]">{{ __('Save: :amount', ['amount' => $product['savings']]) }}</span>
        @endif

        @unless ($product['purchasable'])
            <span class="absolute top-1.5 right-1.5 z-10 rounded-full border border-white/60 bg-white/60 px-2 py-1 text-[10px] leading-none font-semibold text-gray-700 shadow-sm backdrop-blur-md lg:text-[11px]">{{ $product['stock_label'] }}</span>
        @endunless

        <a href="{{ $product['url'] }}" class="skeleton block aspect-square w-full overflow-hidden rounded-lg p-4 lg:p-6" tabindex="-1" aria-hidden="true">
            <img src="{{ $product['image'] }}" alt="" width="400" height="400" loading="lazy" data-fade @class([
                'size-full object-contain transition duration-500 ease-out-expo group-hover:scale-105',
                'opacity-60 grayscale-[30%]' => ! $product['purchasable'],
            ])>
        </a>
    </div>

    <h3 class="mt-3 line-clamp-2 h-8 text-xs leading-4 font-semibold text-gray-900 overflow-hidden lg:h-10 lg:text-sm lg:leading-5">
        <a href="{{ $product['url'] }}" class="transition hover:text-builder">{{ $product['name'] }}</a>
    </h3>

    @if ($product['features'] !== [])
        <ul class="mt-2 hidden list-disc space-y-0.5 pl-3.5 text-[11px] leading-4 text-gray-600 marker:text-gray-400 @[200px]:block lg:text-xs lg:leading-[1.15rem]">
            @foreach ($product['features'] as $feature)
                <li class="truncate">{{ $feature }}</li>
            @endforeach
        </ul>
    @endif

    <div class="mt-auto pt-3">
        <p class="flex min-h-[22px] lg:min-h-[26px] flex-wrap items-baseline gap-x-2">
            <span class="text-sm font-bold text-price lg:text-lg leading-none">{{ $product['price'] }}</span>
            @if ($product['regular_price'])
                <del class="text-[11px] text-gray-400 lg:text-xs leading-none">{{ $product['regular_price'] }}</del>
            @endif
        </p>

        <div class="mt-2.5 grid grid-cols-[minmax(0,1fr)_auto] gap-2 @max-[170px]:grid-cols-1">
            @if ($product['purchasable'])
                <button type="button" data-ripple data-listing-cart data-product-id="{{ $product['id'] }}"
                    class="relative flex h-10 min-w-0 cursor-pointer items-center justify-center gap-1.5 overflow-hidden rounded-lg bg-button px-2.5 text-[11px] font-semibold text-button-text shadow-[0_6px_16px_-6px_color-mix(in_srgb,var(--color-button)_45%,transparent),inset_0_1px_0_rgba(255,255,255,0.25)] transition hover:bg-button-hover active:scale-[0.97] lg:h-9 lg:text-xs">
                    <x-store.icon name="cart" class="size-3.5" />
                    <span class="truncate">{{ __('Add to Cart') }}</span>
                </button>
            @else
                <button type="button" disabled
                    class="flex h-10 min-w-0 cursor-not-allowed items-center justify-center rounded-lg border border-gray-200/80 bg-gray-100/70 px-2.5 text-[11px] font-semibold text-gray-500 backdrop-blur-sm lg:h-9 lg:text-xs">
                    <span class="truncate">{{ $product['stock_label'] }}</span>
                </button>
            @endif

            <button type="button" data-compare data-product-id="{{ $product['id'] }}" aria-pressed="false" title="{{ __('Compare') }}"
                class="flex h-10 min-w-10 cursor-pointer items-center justify-center gap-1.5 rounded-lg border border-white/70 bg-white/50 px-2.5 text-[11px] font-medium text-gray-700 shadow-[inset_0_1px_0_rgba(255,255,255,0.9),0_1px_3px_rgba(15,23,42,0.08)] ring-1 ring-gray-200/70 backdrop-blur-md transition hover:text-builder hover:ring-builder/50 active:scale-[0.97] aria-pressed:bg-builder/10 aria-pressed:text-builder aria-pressed:ring-builder lg:h-9 lg:min-w-9 lg:text-xs" aria-label="{{ __('Add :name to compare', ['name' => $product['name']]) }}">
                <x-store.icon name="compare" class="size-3.5" />
                <span class="hidden @[280px]:inline">{{ __('Compare') }}</span>
            </button>
        </div>
    </div>
</article>
