@props(['groups', 'price', 'filters', 'clearUrl', 'activeCount' => 0])

@php
    $summaryClass = 'flex cursor-pointer list-none items-center justify-between gap-2 py-3.5 text-sm font-semibold text-gray-900 transition hover:text-builder [&::-webkit-details-marker]:hidden';
@endphp

<aside id="listing-filters" {{ $attributes->merge(['class' => 'filter-panel']) }} data-filter-panel aria-label="Product filters">
    <div class="flex h-full flex-col overflow-hidden rounded-none border-gray-200 bg-white lg:h-auto lg:rounded-lg lg:border">
        <div class="flex items-center justify-between gap-2 border-b border-gray-200 px-4 py-3.5">
            <h2 class="text-base font-bold text-gray-900">
                Filters
                @if ($activeCount > 0)
                    <span class="ml-1 rounded-full bg-builder px-1.5 py-0.5 text-[10px] font-semibold text-white">{{ $activeCount }}</span>
                @endif
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ $clearUrl }}" class="text-xs font-medium text-builder hover:underline">Clear All</a>
                <button type="button" data-filter-close class="grid size-8 cursor-pointer place-items-center rounded-md text-gray-600 hover:bg-surface lg:hidden" aria-label="Close filters">
                    <x-store.icon name="x" class="size-5" />
                </button>
            </div>
        </div>

        <div class="scrollbar-thin flex-1 divide-y divide-gray-200 overflow-y-auto overscroll-contain px-4 lg:max-h-[calc(100vh-11rem)]">
            @foreach ($groups as $group)
                @if ($loop->index === 1)
                    @include('components.store.listing.price-range', ['price' => $price, 'filters' => $filters, 'summaryClass' => $summaryClass])
                @endif

                <details class="group/filter" @if ($group['open']) open @endif>
                    <summary class="{{ $summaryClass }}">
                        {{ $group['name'] }}
                        <x-store.icon name="chevron-right" class="size-4 text-gray-600 transition duration-200 group-open/filter:rotate-90" />
                    </summary>

                    @if ($group['options'] === [])
                        <p class="pb-3 text-xs text-gray-500">No options available.</p>
                    @else
                        <ul @class(['space-y-2.5 pb-4', 'max-h-56 overflow-y-auto pr-1' => count($group['options']) > 8])>
                            @foreach ($group['options'] as $option)
                                <li>
                                    <label class="group/option flex cursor-pointer items-center gap-2.5 text-[13px] text-gray-700">
                                        <input type="checkbox" name="{{ $group['input'] }}" value="{{ $option['value'] }}" @checked($option['checked']) data-filter-input
                                            class="size-4 shrink-0 cursor-pointer rounded border-gray-400 accent-builder">
                                        <span class="flex-1 transition group-hover/option:text-gray-950">{{ $option['label'] }}</span>
                                        <span class="text-[11px] text-gray-400 tabular-nums">({{ $option['count'] }})</span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </details>
            @endforeach

            @if (count($groups) < 2)
                @include('components.store.listing.price-range', ['price' => $price, 'filters' => $filters, 'summaryClass' => $summaryClass])
            @endif
        </div>

        <div class="grid grid-cols-2 gap-2 border-t border-gray-200 p-3 lg:hidden">
            <a href="{{ $clearUrl }}" class="flex h-10 items-center justify-center rounded-md border border-gray-300 text-sm font-medium text-gray-700">Reset</a>
            <button type="submit" class="h-10 cursor-pointer rounded-md bg-builder text-sm font-semibold text-white">Apply</button>
        </div>
    </div>
</aside>
