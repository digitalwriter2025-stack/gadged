{{-- Included by filter-sidebar: $price, $filters, $summaryClass, $badgeClass --}}
@php
    $span = $price['max'] - $price['min'];
    $step = match (true) {
        $span >= 20000 => 100,
        $span >= 2000 => 10,
        default => 1,
    };
    $low = $filters['min'] ?? $price['min'];
    $high = $filters['max'] ?? $price['max'];
    $percent = fn (int $value): float => $span > 0 ? round((min(max($value, $price['min']), $price['max']) - $price['min']) / $span * 100, 2) : 0;
    $isCustom = $filters['min'] !== null || $filters['max'] !== null;
    $fieldClass = 'flex h-10 items-center gap-1 rounded-lg border border-gray-300 bg-white px-2.5 text-sm transition focus-within:border-builder focus-within:ring-2 focus-within:ring-builder/15';
    $inputClass = 'w-full min-w-0 [appearance:textfield] bg-transparent text-gray-900 tabular-nums focus:outline-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none';
@endphp

<details class="filter-group group/filter" open data-filter-group>
    <summary class="{{ $summaryClass }}">
        <span class="min-w-0 flex-1 truncate">{{ __('Price Range') }}</span>
        @if ($isCustom)
            <span class="{{ $badgeClass }} bg-builder/10 text-builder">{{ \App\Support\Localization::digits(1) }}</span>
        @endif
        <x-store.icon name="chevron-down" class="size-4 text-gray-500 transition duration-200 group-open/filter:rotate-180" />
    </summary>

    <div class="space-y-4 pt-1 pb-4" data-price-range>
        @if ($span > 0)
            <div class="relative h-[18px]">
                <div class="absolute inset-x-[9px] top-1/2 h-1 -translate-y-1/2 rounded-full bg-gray-200" data-price-track style="--from: {{ $percent($low) }}%; --to: {{ $percent($high) }}%;">
                    <div class="absolute inset-y-0 right-[calc(100%-var(--to))] left-(--from) rounded-full bg-builder"></div>
                </div>
                <input type="range" min="{{ $price['min'] }}" max="{{ $price['max'] }}" step="{{ $step }}" value="{{ min(max($low, $price['min']), $price['max']) }}" data-price-slider="min" aria-label="{{ __('Minimum price') }}" class="price-slider">
                <input type="range" min="{{ $price['min'] }}" max="{{ $price['max'] }}" step="{{ $step }}" value="{{ min(max($high, $price['min']), $price['max']) }}" data-price-slider="max" aria-label="{{ __('Maximum price') }}" class="price-slider">
            </div>
        @endif

        <div class="grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-end gap-2">
            <label class="min-w-0">
                <span class="mb-1 block text-[11px] text-gray-500" aria-hidden="true">{{ __('Minimum') }}</span>
                <span class="{{ $fieldClass }}">
                    <span class="text-xs text-gray-400">৳</span>
                    <span class="sr-only">{{ __('Minimum price') }}</span>
                    <input type="number" name="min" inputmode="numeric" min="0" value="{{ $low }}" data-default="{{ $price['min'] }}" data-filter-price class="{{ $inputClass }}">
                </span>
            </label>
            <span class="mb-5 h-px w-2.5 bg-gray-400" aria-hidden="true"></span>
            <label class="min-w-0">
                <span class="mb-1 block text-[11px] text-gray-500" aria-hidden="true">{{ __('Maximum') }}</span>
                <span class="{{ $fieldClass }}">
                    <span class="text-xs text-gray-400">৳</span>
                    <span class="sr-only">{{ __('Maximum price') }}</span>
                    <input type="number" name="max" inputmode="numeric" min="0" value="{{ $high }}" data-default="{{ $price['max'] }}" data-filter-price class="{{ $inputClass }}">
                </span>
            </label>
        </div>
    </div>
</details>
