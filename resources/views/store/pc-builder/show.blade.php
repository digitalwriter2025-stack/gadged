<x-layouts.store :title="$seo['title']" :description="$seo['description']">
    <x-slot:head>
        <link rel="canonical" href="{{ route('store.pc-builder.show') }}">
    </x-slot:head>

    <x-store.breadcrumbs :items="$breadcrumbs" class="print:hidden" />

    <div class="bg-surface py-4 lg:py-6" data-pc-builder>
        <div class="mx-auto max-w-[58rem] px-2.5 sm:px-4">
            <div class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-gray-200 bg-gray-50 px-3 py-2.5 sm:px-4 sm:py-3 lg:px-6">
                    <span class="rounded bg-topbar px-3 py-2 print:bg-transparent">
                        <x-store.logo class="h-8 w-auto lg:h-10" />
                    </span>

                    <div class="flex items-center divide-x divide-gray-200 print:hidden">
                        <button type="button" data-print class="group flex cursor-pointer flex-col items-center gap-1 px-2.5 text-[11px] text-gray-600 sm:px-3 transition hover:text-accent lg:px-4 lg:text-xs">
                            <x-store.icon name="printer" class="size-5 text-accent transition group-hover:scale-110" />
                            <span class="sr-only sm:not-sr-only">{{ __('Print') }}</span>
                        </button>
                        <form method="POST" action="{{ route('store.pc-builder.clear') }}" data-confirm="{{ __('Remove every component from your build?') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" @disabled($itemCount === 0) class="group flex cursor-pointer flex-col items-center gap-1 px-2.5 text-[11px] text-gray-600 sm:px-3 transition hover:text-accent disabled:cursor-not-allowed disabled:opacity-50 lg:px-4 lg:text-xs">
                                <x-store.icon name="trash" class="size-5 text-accent transition group-hover:scale-110" />
                                <span class="sr-only sm:not-sr-only">{{ __('Clear') }}</span>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="pt-3 pb-1 sm:px-6 sm:py-5 lg:px-12 lg:py-7">
                    <div class="flex flex-col items-center gap-3 px-3 sm:flex-row sm:flex-wrap sm:items-start sm:justify-between sm:gap-4 sm:px-0">
                        <div>
                            <h1 class="sr-only text-sm font-semibold text-builder sm:not-sr-only lg:text-base">{{ __('PC Builder - Build Your Own Computer') }}</h1>
                            <label class="inline-flex sm:mt-2 cursor-pointer items-center gap-2 text-xs text-gray-600 print:hidden">
                                <input type="checkbox" data-hide-unconfigured class="size-4 rounded border-gray-300 accent-builder">
                                {{ __('Hide Unconfigured Components') }}
                            </label>
                        </div>

                        <div class="flex w-full max-w-[16rem] items-stretch gap-2 sm:w-auto sm:max-w-none sm:gap-3">
                            <div class="relative flex min-w-[6.5rem] flex-1 flex-col items-center justify-center rounded-md border border-dashed border-accent px-3 py-2 text-center">
                                <span class="absolute top-0.5 right-1.5 text-[9px] font-semibold text-builder">{{ __('EST.') }}</span>
                                <span class="text-lg leading-none font-bold text-gray-900">{{ \App\Support\Localization::digits($wattage) }}W</span>
                                <span class="mt-1 text-[10px] text-gray-500">{{ __('Estimated Wattage') }}</span>
                            </div>
                            <div class="flex min-w-[6.5rem] flex-1 flex-col items-center justify-center rounded-md bg-builder px-4 py-2 text-center text-white">
                                <span class="text-lg leading-none font-bold" data-build-total>{{ $total }}</span>
                                <span class="mt-1 text-[10px] font-medium">{{ trans_choice(':count Item|:count Items', $itemCount, ['count' => \App\Support\Localization::digits($itemCount)]) }}</span>
                            </div>
                        </div>
                    </div>

                    @if (session('status'))
                        <p class="mt-4 mx-3 rounded-md bg-green-50 px-3 py-2 text-xs text-savings sm:mx-0 print:hidden" role="status">{{ session('status') }}</p>
                    @endif

                    @if ($itemCount > 0 && $missingRequired !== [])
                        <p class="mt-4 mx-3 rounded-md bg-amber-50 px-3 py-2 text-xs text-amber-800 sm:mx-0 print:hidden">{{ __('Still required: :components', ['components' => implode(', ', $missingRequired)]) }}</p>
                    @endif

                    @foreach (['core' => __('Core Components'), 'peripherals' => __('Peripherals & Others')] as $groupKey => $groupLabel)
                        <section class="mt-4 sm:mt-5" aria-labelledby="pc-group-{{ $groupKey }}">
                            <h2 id="pc-group-{{ $groupKey }}" class="bg-gray-500 px-2 py-1 text-[11px] font-semibold text-white lg:text-xs">{{ $groupLabel }}</h2>

                            <ul class="px-2.5 sm:px-0">
                                @foreach ($groups[$groupKey] as $slot)
                                    <x-store.pc-builder-slot :item="$slot" />
                                @endforeach
                            </ul>
                        </section>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layouts.store>
