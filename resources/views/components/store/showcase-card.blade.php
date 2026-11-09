@props(['product'])

<article class="group relative flex h-full flex-1 flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-black/5 transition duration-300 ease-out-expo hover:-translate-y-1 hover:shadow-[0_18px_32px_-14px_rgba(0,0,0,0.55)]">
    <div class="relative bg-gray-50">
        <a href="{{ $product['url'] }}" class="skeleton block aspect-square w-full overflow-hidden p-3 @xl:p-4" tabindex="-1" aria-hidden="true">
            <img src="{{ asset($product['image']) }}" alt="" width="400" height="400" loading="lazy" data-fade class="size-full object-contain mix-blend-multiply transition duration-500 ease-out-expo group-hover:scale-105">
        </a>

        <div class="pointer-events-none absolute inset-x-2 top-2 z-10 flex items-start justify-between gap-1.5">
            @if ($product['savings'])
                <span class="rounded-md bg-green-100 px-1.5 py-1 text-[11px] leading-none font-bold whitespace-nowrap text-green-800 @xl:text-xs">{{ __('Save :amount', ['amount' => $product['savings']]) }}</span>
            @else
                <span></span>
            @endif

            <button type="button" data-wishlist-toggle aria-pressed="false" data-analytics-item="{{ json_encode(['item_id' => (string) $product['id'], 'item_name' => $product['name']]) }}" aria-label="{{ __('Save :name to wishlist', ['name' => $product['name']]) }}" class="pointer-events-auto grid size-8 shrink-0 cursor-pointer place-items-center rounded-full border border-gray-200 bg-white text-gray-700 shadow-sm transition hover:scale-110 hover:border-gray-800 hover:text-gray-900 active:scale-90 aria-pressed:border-accent aria-pressed:text-accent">
                <x-store.icon name="heart" class="size-4 transition in-aria-pressed:fill-current" />
            </button>
        </div>
    </div>

    <div class="flex flex-1 flex-col p-3 @xl:px-3.5">
        <h3 class="line-clamp-2 h-9 text-[13px] leading-[18px] text-gray-700 overflow-hidden @xl:h-10 @xl:text-sm @xl:leading-5">
            <a href="{{ $product['url'] }}" class="transition group-hover:text-accent group-hover:underline after:absolute after:inset-0 after:content-['']">{{ $product['name'] }}</a>
        </h3>

        <div class="mt-auto pt-3">
            <p class="text-base leading-none font-bold tracking-tight text-gray-900 @xl:text-lg">{{ $product['price'] }}</p>
            <div class="mt-1.5 min-h-[16px]">
                @if (filled($product['regular_price']))
                    <del class="block text-xs leading-none text-gray-400">{{ $product['regular_price'] }}</del>
                @endif
            </div>
        </div>
    </div>
</article>
