@props(['groups', 'price', 'filters', 'clearUrl', 'activeCount' => 0])

@php
    $summaryClass = 'flex cursor-pointer list-none items-center gap-2 py-3.5 text-sm font-semibold text-gray-900 select-none transition hover:text-black focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-builder [&::-webkit-details-marker]:hidden';
    $badgeClass = 'grid h-5 min-w-5 place-items-center rounded-full px-1.5 text-[10px] font-semibold tabular-nums';
@endphp

<aside id="listing-filters" {{ $attributes->merge(['class' => 'filter-panel']) }} data-filter-panel aria-label="{{ __('Product filters') }}">
    <div class="flex h-full flex-col overflow-hidden bg-white lg:h-auto lg:rounded-xl lg:border lg:border-gray-200">
        <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 lg:py-3.5">
            <h2 class="flex items-center gap-2 text-base font-bold text-gray-900">
                <x-store.icon name="filter" class="size-4 text-gray-500" />
                {{ __('Filters') }}
                @if ($activeCount > 0)
                    <span class="{{ $badgeClass }} bg-builder text-white">{{ \App\Support\Localization::digits($activeCount) }}</span>
                @endif
            </h2>
            <div class="flex items-center gap-1">
                @if ($activeCount > 0)
                    <a href="{{ $clearUrl }}" class="rounded-md px-2 py-1 text-xs font-medium text-accent transition hover:bg-accent/5">{{ __('Clear All') }}</a>
                @endif
                <button type="button" data-filter-close class="-mr-1.5 grid size-9 cursor-pointer place-items-center rounded-full text-gray-600 transition hover:bg-surface hover:text-gray-900 lg:hidden" aria-label="{{ __('Close filters') }}">
                    <x-store.icon name="x" class="size-5" />
                </button>
            </div>
        </div>

        <div class="scrollbar-thin flex-1 divide-y divide-gray-100 overflow-y-auto overscroll-contain px-4 lg:max-h-[calc(100vh-11rem)]" data-lenis-prevent>
            @foreach ($groups as $group)
                @if ($loop->index === 1)
                    @include('components.store.listing.price-range', ['price' => $price, 'filters' => $filters, 'summaryClass' => $summaryClass, 'badgeClass' => $badgeClass])
                @endif

                @php
                    $selectedCount = count(array_filter(array_column($group['options'], 'checked')));
                    $isSearchable = count($group['options']) > 8;
                @endphp

                <details class="filter-group group/filter" @if ($group['open']) open @endif data-filter-group>
                    <summary class="{{ $summaryClass }}">
                        <span class="min-w-0 flex-1 truncate">{{ $group['name'] }}</span>
                        @if ($selectedCount > 0)
                            <span class="{{ $badgeClass }} bg-builder/10 text-builder">{{ \App\Support\Localization::digits($selectedCount) }}</span>
                        @endif
                        <x-store.icon name="chevron-down" class="size-4 text-gray-500 transition duration-200 group-open/filter:rotate-180" />
                    </summary>

                    <div class="pb-4">
                        @if ($group['options'] === [])
                            <p class="text-xs text-gray-500">{{ __('No options available.') }}</p>
                        @else
                            @if ($isSearchable)
                                <label class="mb-2 flex h-9 items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-2.5 transition focus-within:border-builder focus-within:bg-white">
                                    <x-store.icon name="search" class="size-3.5 text-gray-400" />
                                    <span class="sr-only">{{ __('Search :name', ['name' => $group['name']]) }}</span>
                                    <input type="search" data-filter-search autocomplete="off" placeholder="{{ __('Search :name', ['name' => $group['name']]) }}"
                                        class="h-full w-full min-w-0 bg-transparent text-xs text-gray-900 placeholder:text-gray-400 focus:outline-none">
                                </label>
                            @endif

                            <ul @class(['-mx-1.5 space-y-0.5', 'scrollbar-thin max-h-60 overflow-y-auto overscroll-contain pr-1' => $isSearchable]) @if ($isSearchable) data-lenis-prevent @endif>
                                @foreach ($group['options'] as $option)
                                    @php
                                        $isUnavailable = $option['count'] === 0 && ! $option['checked'];
                                    @endphp
                                    <li data-filter-option="{{ \Illuminate\Support\Str::lower($option['label']) }}">
                                        <label @class([
                                            'flex items-center gap-2.5 rounded-md px-1.5 py-1.5 text-[13px] transition',
                                            'cursor-pointer text-gray-700 hover:bg-surface' => ! $isUnavailable,
                                            'cursor-not-allowed text-gray-400' => $isUnavailable,
                                        ])>
                                            <input type="checkbox" name="{{ $group['input'] }}" value="{{ $option['value'] }}" @checked($option['checked']) @disabled($isUnavailable) data-filter-input
                                                class="filter-check peer">
                                            <span class="min-w-0 flex-1 leading-5 break-words peer-checked:font-medium peer-checked:text-gray-900">{{ $option['label'] }}</span>
                                            <span class="shrink-0 rounded-full bg-surface px-1.5 py-px text-[10px] text-gray-500 tabular-nums peer-checked:bg-builder peer-checked:text-white">{{ \App\Support\Localization::digits($option['count']) }}</span>
                                        </label>
                                    </li>
                                @endforeach
                            </ul>

                            @if ($isSearchable)
                                <p hidden data-filter-search-empty class="py-2 text-xs text-gray-500">{{ __('No matching options.') }}</p>
                            @endif
                        @endif
                    </div>
                </details>
            @endforeach

            @if (count($groups) < 2)
                @include('components.store.listing.price-range', ['price' => $price, 'filters' => $filters, 'summaryClass' => $summaryClass, 'badgeClass' => $badgeClass])
            @endif
        </div>

        <div class="grid grid-cols-[auto_minmax(0,1fr)] gap-2 border-t border-gray-200 bg-white px-4 pt-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] lg:hidden">
            <a href="{{ $clearUrl }}" class="flex h-11 items-center justify-center gap-1.5 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 transition hover:bg-surface">
                <x-store.icon name="refresh-cw" class="size-4" />
                {{ __('Reset') }}
            </a>
            <button type="submit" class="h-11 cursor-pointer rounded-lg bg-button text-sm font-semibold text-button-text transition hover:bg-button-hover active:scale-[0.98]">{{ __('Apply') }}</button>
        </div>
    </div>
</aside>
