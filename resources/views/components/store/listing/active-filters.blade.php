@props(['groups', 'filters', 'price', 'clearUrl'])

@php
    $chips = \App\Support\Storefront\ActiveListingFilters::chips($groups, $filters, $price, url()->current(), request()->query());
@endphp

@if ($chips !== [])
    <div class="flex flex-wrap items-center gap-2" data-active-filters>
        <span class="sr-only">{{ __('Active filters') }}</span>
        @foreach ($chips as $chip)
            <a href="{{ $chip['url'] }}" rel="nofollow"
                class="group/chip inline-flex h-8 max-w-full items-center gap-1.5 rounded-full border border-gray-200 bg-white pr-1.5 pl-3 text-xs text-gray-800 shadow-xs transition hover:border-gray-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-builder"
                aria-label="{{ __('Remove filter :filter', ['filter' => $chip['group'].': '.$chip['label']]) }}">
                <span class="hidden text-gray-500 sm:inline">{{ $chip['group'] }}:</span>
                <span class="truncate font-medium">{{ $chip['label'] }}</span>
                <span class="grid size-5 shrink-0 place-items-center rounded-full bg-gray-100 text-gray-500 transition group-hover/chip:bg-accent group-hover/chip:text-white">
                    <x-store.icon name="x" class="size-3" />
                </span>
            </a>
        @endforeach
        @if (count($chips) > 1)
            <a href="{{ $clearUrl }}" class="inline-flex h-8 items-center px-2 text-xs font-medium text-accent underline-offset-2 hover:underline">{{ __('Clear All') }}</a>
        @endif
    </div>
@endif
