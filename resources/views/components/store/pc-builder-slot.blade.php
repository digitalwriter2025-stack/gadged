@props(['item'])

<li @class(['flex items-center gap-3 border-b border-gray-200 py-3.5 last:border-b-0 sm:gap-4 sm:py-3', 'print:hidden' => $item['product'] === null]) @if ($item['product'] === null) data-unconfigured @endif>
    <span class="grid size-14 shrink-0 place-items-center overflow-hidden rounded bg-gray-100 text-builder">
        @if ($item['product'])
            <img src="{{ $item['product']['image'] }}" alt="" width="56" height="56" loading="lazy" class="size-full object-contain p-1">
        @else
            <x-store.icon :name="$item['icon']" class="size-7" />
        @endif
    </span>

    <div class="min-w-0 flex-1">
        <p class="flex flex-wrap items-center gap-1.5 text-[11px] text-gray-600 lg:text-xs">
            {{ $item['label'] }}
            @if ($item['required'])
                <span class="rounded-sm bg-gray-600 px-1 text-[10px] leading-4 text-white">{{ __('Required') }}</span>
            @endif
        </p>

        @if ($item['product'])
            <a href="{{ $item['product']['url'] }}" class="mt-1 line-clamp-2 text-xs font-medium text-gray-900 transition hover:text-accent lg:text-[13px]">{{ $item['product']['name'] }}</a>
        @else
            <span class="mt-2.5 block h-2 w-full max-w-[16rem] rounded bg-gray-100 sm:h-2.5" aria-hidden="true"></span>
        @endif
    </div>

    <div class="hidden w-24 shrink-0 text-right sm:block">
        @if ($item['product'])
            <span class="text-sm font-bold text-gray-900">{{ $item['product']['price'] }}</span>
        @else
            <span class="ml-auto block h-2.5 w-14 rounded bg-gray-100" aria-hidden="true"></span>
        @endif
    </div>

    <div class="flex shrink-0 items-center gap-1.5 print:hidden">
        @if ($item['product'])
            <span class="text-xs font-bold text-gray-900 sm:hidden">{{ $item['product']['price'] }}</span>
            <a href="{{ $item['choose_url'] }}" class="grid size-9 place-items-center rounded border border-button text-button transition hover:bg-button hover:text-button-text" aria-label="{{ __('Change :component', ['component' => $item['label']]) }}">
                <x-store.icon name="compare" class="size-4" />
            </a>
            <form method="POST" action="{{ $item['remove_url'] }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="grid size-9 cursor-pointer place-items-center rounded border border-gray-300 text-gray-500 transition hover:border-button hover:bg-button hover:text-button-text" aria-label="{{ __('Remove :component', ['component' => $item['label']]) }}">
                    <x-store.icon name="x" class="size-4" />
                </button>
            </form>
        @else
            <a href="{{ $item['choose_url'] }}" class="rounded border-2 border-button px-4 py-2 text-xs font-semibold sm:py-1.5 text-button transition hover:bg-button hover:text-button-text lg:px-5 lg:py-2">{{ __('Choose') }}</a>
        @endif
    </div>
</li>
