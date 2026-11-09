@php
    $quickLinks = $headerSettings['quick_links'] ?? [];
    $accountLinks = array_values(array_filter([
        isset($quickLinks['track']) ? ['key' => 'track', 'icon' => 'truck', ...$quickLinks['track'], 'active' => request()->routeIs('store.orders.*')] : null,
        isset($quickLinks['combo']) ? ['key' => 'combo', 'icon' => 'package', ...$quickLinks['combo'], 'active' => request()->routeIs('store.combos.*')] : null,
        auth()->check()
            ? ['key' => 'account', 'icon' => 'user', 'label' => __('Account'), 'caption' => __('Hi, :name', ['name' => str(auth()->user()->name)->before(' ')->limit(14)->toString()]), 'url' => route('store.account'), 'active' => request()->routeIs('store.account')]
            : ['key' => 'account', 'icon' => 'user', 'label' => __('Account'), 'caption' => __('Register or Login'), 'url' => route('login'), 'active' => request()->routeIs('login', 'register')],
    ]));
@endphp

<header class="site-header sticky top-0 z-30 shadow-[0_2px_6px_rgba(0,0,0,0.12)]" data-site-header>
    @if (!empty($announcement['enabled']) && filled($announcement['text'] ?? null))
        <div style="background-color: {{ $announcement['bg_color'] }}; color: {{ $announcement['text_color'] }};" class="relative z-40 py-2 px-4 text-center text-xs font-semibold sm:text-sm transition-colors duration-300">
            @if (filled($announcement['link'] ?? null))
                <a href="{{ $announcement['link'] }}" class="inline-flex items-center justify-center gap-1.5 hover:underline">
                    <span>{{ $announcement['text'] }}</span>
                    <x-store.icon name="arrow-right" class="size-3.5" />
                </a>
            @else
                <span>{{ $announcement['text'] }}</span>
            @endif
        </div>
    @endif
    <div class="bg-topbar text-white">
        <div class="mx-auto grid max-w-hero grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-x-2 gap-y-2.5 px-3 py-2.5 min-[360px]:gap-x-3 sm:gap-x-4 sm:px-4 lg:flex lg:h-[80px] lg:gap-x-5 lg:py-0 xl:gap-x-6">
            <button type="button" data-drawer-open="mobile-menu" aria-controls="mobile-menu" aria-expanded="false" class="-ml-1 grid size-12 cursor-pointer place-items-center rounded-md transition hover:bg-white/10 active:scale-90 lg:hidden" aria-label="{{ __('Open menu') }}">
                <x-store.icon name="menu" class="size-8" />
            </button>

            <a href="{{ route('store.home') }}" class="block min-w-0 justify-self-start transition duration-300 hover:opacity-85 lg:shrink-0" aria-label="{{ __(':name home', ['name' => config('app.name')]) }}">
                <x-store.logo class="h-9 w-auto max-w-full object-contain object-left sm:h-10 lg:h-11 xl:h-12" />
            </a>

            <form action="{{ route('store.search') }}" method="GET" role="search" data-search data-search-url="{{ route('store.search.suggest') }}" class="group/search relative order-last col-span-full w-full lg:order-none lg:mx-auto lg:w-auto lg:max-w-[580px] lg:min-w-[220px] lg:flex-1">
                <label for="site-search" class="sr-only">{{ __('Search products') }}</label>
                <div class="flex h-10 items-center rounded-md bg-white ring-2 ring-transparent transition duration-300 focus-within:ring-black/25 lg:h-[42px] lg:rounded-md">
                    <input id="site-search" type="search" name="q" value="{{ request()->routeIs('store.search') ? request()->query('q') : '' }}" maxlength="100" required autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="site-search-results" placeholder="{{ __('Search for products, brands…') }}" data-search-input class="h-full w-full rounded-md bg-transparent px-3 text-sm text-gray-900 placeholder:text-gray-500 focus:outline-none lg:px-5 lg:text-[15px] [&::-webkit-search-cancel-button]:appearance-none">
                    <kbd data-search-hint class="mr-1 hidden shrink-0 rounded border border-gray-300 px-1.5 font-sans text-[11px] leading-5 text-gray-400 lg:block" title="{{ __('Press / to search') }}">/</kbd>
                    <button type="button" hidden data-search-clear class="grid size-8 shrink-0 cursor-pointer place-items-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-gray-700" aria-label="{{ __('Clear search') }}">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                    <button type="submit" class="grid h-full w-11 shrink-0 cursor-pointer place-items-center text-gray-800 transition group-focus-within/search:text-accent lg:w-12" aria-label="{{ __('Search') }}">
                        <x-store.icon name="search" class="size-5 lg:size-[22px]" />
                    </button>
                </div>
                <ul id="site-search-results" role="listbox" hidden data-search-results class="absolute inset-x-0 top-full z-50 mt-1.5 max-h-[75vh] overflow-y-auto overscroll-contain rounded-lg bg-white text-gray-900 shadow-2xl ring-1 ring-black/5 transition duration-150 ease-out starting:-translate-y-1 starting:opacity-0"></ul>
            </form>

            <nav class="flex items-center gap-0.5 justify-self-end sm:gap-1.5 lg:shrink-0 lg:gap-3 xl:gap-5 2xl:gap-8" aria-label="{{ __('Account and offers') }}">
                @if ($showLanguageSwitcher ?? true)
                    <x-store.language-switcher class="mr-1 sm:mr-0" />
                @endif
                @foreach ($accountLinks as $link)
                    <a href="{{ $link['url'] }}" title="{{ $link['label'] }}" @class([
                        'group items-center gap-2 rounded-md p-2 transition hover:bg-white/10 xl:p-0 xl:hover:bg-transparent',
                        'flex' => $link['key'] === 'account',
                        'hidden sm:flex' => $link['key'] !== 'account',
                    ]) aria-label="{{ $link['label'] }}" @if ($link['active']) aria-current="page" @endif>
                        <x-store.icon :name="$link['icon']" class="size-5 text-white transition duration-300 ease-spring group-hover:scale-125 lg:size-[22px]" />
                        <span class="hidden leading-tight xl:ml-1.5 xl:block">
                            <span class="block text-[15px] font-medium transition group-hover:text-white/80">{{ $link['label'] }}</span>
                            <span class="mt-0.5 block text-xs text-white/75">{{ $link['caption'] }}</span>
                        </span>
                    </a>
                @endforeach
                <a href="{{ route('store.checkout.show') }}" title="{{ __('Cart') }}" class="group hidden items-center gap-2 rounded-md p-2 transition hover:bg-white/10 lg:flex xl:p-0 xl:hover:bg-transparent" aria-label="{{ trans_choice('Cart, :count item|Cart, :count items', $cartCount ?? 0, ['count' => \App\Support\Localization::digits($cartCount ?? 0)]) }}">
                    <span class="relative">
                        <x-store.icon name="cart" class="size-5 text-white transition duration-300 ease-spring group-hover:scale-125 lg:size-[22px]" />
                        @if (($cartCount ?? 0) > 0)
                            <span class="absolute -top-2 -right-2 grid h-4 min-w-4 place-items-center rounded-full bg-white px-1 text-[10px] leading-none font-semibold text-accent">{{ \App\Support\Localization::digits($cartCount) }}</span>
                        @endif
                    </span>
                    <span class="hidden leading-tight xl:ml-1.5 xl:block">
                        <span class="block text-[15px] font-medium transition group-hover:text-white/80">{{ __('Cart') }}</span>
                        <span class="mt-0.5 block text-xs text-white/75">{{ trans_choice(':count Item|:count Items', $cartCount ?? 0, ['count' => \App\Support\Localization::digits($cartCount ?? 0)]) }}</span>
                    </span>
                </a>
                @if ($headerSettings['show_pc_builder'] ?? false)
                    <a href="{{ route('store.pc-builder.show') }}" data-ripple class="relative ml-1 hidden overflow-hidden rounded-md bg-white px-3 py-2 text-xs font-semibold whitespace-nowrap text-accent transition hover:-translate-y-0.5 hover:bg-builder hover:text-white hover:shadow-lg hover:shadow-black/30 sm:block lg:px-4 lg:py-2.5 xl:ml-0 xl:px-7 xl:py-3 xl:text-sm">{{ __('PC Builder') }}</a>
                @endif
            </nav>
        </div>
    </div>

    <nav class="hidden border-b border-gray-200 bg-white shadow-sm lg:block" aria-label="{{ __('Product categories') }}" data-mega-nav>
        <ul class="mx-auto flex h-11 max-w-hero items-center justify-center gap-x-7 px-4 xl:gap-x-10">
            @foreach (array_slice($navigationLinks, 0, \App\Support\Storefront\NavigationMenu::HEADER_LIMIT) as $link)
                <li class="group/nav relative flex h-full shrink-0 items-center" @if ($link['children'] !== []) data-mega-item @endif>
                    <a href="{{ $link['url'] }}" @if ($link['children'] !== []) aria-haspopup="true" aria-expanded="false" @endif class="nav-underline text-[12px] font-medium whitespace-nowrap xl:text-[13px] 2xl:text-sm text-gray-900 transition hover:text-accent">{{ $link['label'] }}</a>

                    @if ($link['children'] !== [])
                        <x-store.mega-menu :items="$link['children']" :align-right="$loop->remaining < 4" />
                    @endif
                </li>
            @endforeach
            <li class="flex h-full shrink-0 items-center">
                <a href="{{ route('store.trending') }}" class="nav-underline inline-flex items-center gap-1 text-[12px] font-semibold whitespace-nowrap text-accent transition hover:text-accent-dark xl:text-[13px] 2xl:text-sm" @if (request()->routeIs('store.trending')) aria-current="page" @endif>
                    <x-store.icon name="bolt" class="size-3.5" />
                    {{ __('Trending') }}
                </a>
            </li>
        </ul>
    </nav>
</header>

@include('store.partials.mobile-drawer', ['accountLinks' => $accountLinks])
