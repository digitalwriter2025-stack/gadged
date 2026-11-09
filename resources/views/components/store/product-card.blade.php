@props(['product', 'flashSale' => false])

<article class="group relative flex h-full flex-1 flex-col rounded-lg border border-gray-100 bg-white p-3 shadow-[0_1px_4px_rgba(15,23,42,0.08)] transition duration-300 ease-out-expo hover:-translate-y-1 hover:border-accent/30 hover:shadow-[0_12px_24px_-12px_rgba(15,23,42,0.25)] lg:p-4">
    <div class="relative">
        <a href="{{ $product['url'] }}" class="skeleton block aspect-square w-full overflow-hidden rounded-md bg-gray-50 p-3" tabindex="-1" aria-hidden="true">
            <img src="{{ asset($product['image']) }}" alt="" width="400" height="400" loading="lazy" data-fade class="size-full object-contain mix-blend-multiply transition duration-500 ease-out-expo group-hover:scale-110">
        </a>

        @if ($product['savings'])
            <span class="shine absolute top-2 left-2 z-10 rounded-sm bg-savings px-1.5 py-1 text-[10px] leading-none font-semibold text-white lg:text-[11px]">{{ __('Save: :amount', ['amount' => $product['savings']]) }}</span>
        @endif
    </div>

    <h3 class="mt-3 line-clamp-2 h-8 text-xs leading-4 font-medium text-gray-800 overflow-hidden lg:h-10 lg:text-sm lg:leading-5">
        <a href="{{ $product['url'] }}" class="transition group-hover:text-accent after:absolute after:inset-0 after:content-['']">{{ $product['name'] }}</a>
    </h3>

    <div class="mt-2 flex min-h-[22px] lg:min-h-[26px] flex-wrap items-baseline justify-center gap-x-2">
        <span class="text-sm font-bold text-price lg:text-lg leading-none">{{ $product['price'] }}</span>
        @if ($product['regular_price'])
            <del class="text-[11px] text-gray-400 lg:text-xs leading-none">{{ $product['regular_price'] }}</del>
        @endif
    </div>

    @if ($flashSale)
        <div class="mt-auto">
            <div class="countdown mt-2.5 grid grid-cols-4 gap-1 rounded-md border border-accent/40 bg-accent/[0.03] p-1.5 transition-colors" data-countdown data-ends-at="{{ $product['ends_at'] }}" role="timer" aria-label="{{ __('Deal ends in') }}">
                @foreach (['days' => __('Days'), 'hours' => __('Hrs'), 'minutes' => __('Min'), 'seconds' => __('Sec')] as $unit => $label)
                    <div class="countdown-col flex flex-col items-center justify-center rounded bg-white py-1 px-0.5 text-center leading-none border border-gray-100/90 shadow-[0_1px_2px_rgba(0,0,0,0.03)] {{ $unit === 'seconds' ? 'countdown-sec border-accent/30 ring-1 ring-accent/20 bg-accent/[0.02]' : '' }}">
                        <span class="countdown-num block text-xs font-bold text-gray-800 tabular-nums lg:text-sm {{ $unit === 'seconds' ? 'text-accent' : '' }}" data-unit="{{ $unit }}">{{ \App\Support\Localization::digits('00') }}</span>
                        <span class="mt-1 block text-[8.5px] font-medium text-gray-500 uppercase tracking-tight sm:text-[9.5px]">{{ $label }}</span>
                    </div>
                @endforeach
            </div>

            <a href="{{ $product['url'] }}" data-ripple data-deal-pulse class="relative z-10 mt-3 flex h-9 items-center justify-center gap-1 overflow-hidden rounded-md bg-button text-xs font-semibold text-button-text transition hover:bg-button-hover hover:shadow-md hover:shadow-button/30 active:scale-[0.97] lg:h-10 lg:text-sm">
                <x-store.icon name="bolt" class="size-3.5 animate-bolt lg:size-4" /> {{ __('View Deal') }}
            </a>
        </div>
    @else
        <div class="mt-auto pt-3">
            <a href="{{ $product['url'] }}" data-ripple class="relative z-10 flex h-9 items-center justify-center gap-1 overflow-hidden rounded-md bg-button text-xs font-semibold text-button-text transition hover:bg-button-hover active:scale-[0.97] lg:h-10 lg:text-sm">
                {{ __('View Details') }}
                <x-store.icon name="arrow-right" class="size-3 -translate-x-1 opacity-0 transition duration-300 group-hover:translate-x-0 group-hover:opacity-100" />
            </a>
        </div>
    @endif
</article>
