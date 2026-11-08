@php
    $accountLinks = [
        ['icon' => 'gift', 'label' => 'Offers', 'caption' => 'Latest Offers', 'url' => route('store.offers.index')],
        ['icon' => 'bolt', 'label' => 'Mobile Deal', 'caption' => 'Special Deals', 'url' => route('store.offers.mobile')],
        auth()->check()
            ? ['icon' => 'user', 'label' => 'Account', 'caption' => str(auth()->user()->name)->before(' ')->limit(14)->prepend('Hi, ')->toString(), 'url' => '#']
            : ['icon' => 'user', 'label' => 'Account', 'caption' => 'Register or Login', 'url' => Route::has('login') ? route('login') : '#'],
    ];
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
            <button type="button" data-drawer-open="mobile-menu" aria-controls="mobile-menu" aria-expanded="false" class="-ml-1 grid size-12 cursor-pointer place-items-center rounded-md transition hover:bg-white/10 active:scale-90 lg:hidden" aria-label="Open menu">
                <x-store.icon name="menu" class="size-8" />
            </button>

            <a href="{{ route('store.home') }}" class="block min-w-0 justify-self-start transition duration-300 hover:opacity-85 lg:shrink-0" aria-label="{{ config('app.name') }} home">
                <x-store.logo class="h-9 w-auto max-w-full object-contain object-left sm:h-10 lg:h-[52px] xl:h-14" />
            </a>

            <form action="{{ route('store.search') }}" method="GET" role="search" data-search data-search-url="{{ route('store.search.suggest') }}" class="group/search relative order-last col-span-full w-full lg:order-none lg:mx-auto lg:w-auto lg:max-w-[580px] lg:min-w-[220px] lg:flex-1">
                <label for="site-search" class="sr-only">Search products</label>
                <div class="flex h-10 items-center rounded-md bg-white ring-2 ring-transparent transition duration-300 focus-within:ring-black/25 lg:h-[42px] lg:rounded-md">
                    <input id="site-search" type="search" name="q" value="{{ request()->routeIs('store.search') ? request()->query('q') : '' }}" maxlength="100" required autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="site-search-results" placeholder="Search for products, brands…" data-search-input class="h-full w-full rounded-md bg-transparent px-3 text-sm text-gray-900 placeholder:text-gray-500 focus:outline-none lg:px-5 lg:text-[15px]">
                    <button type="submit" class="grid h-full w-11 shrink-0 cursor-pointer place-items-center text-gray-800 transition group-focus-within/search:text-accent lg:w-12" aria-label="Search">
                        <x-store.icon name="search" class="size-5 lg:size-[22px]" />
                    </button>
                </div>
                <ul id="site-search-results" role="listbox" hidden data-search-results class="absolute inset-x-0 top-full z-50 mt-1 max-h-[70vh] overflow-y-auto rounded-md bg-white py-1 text-gray-900 shadow-xl ring-1 ring-black/5"></ul>
            </form>

            <nav class="flex items-center gap-0.5 justify-self-end sm:gap-1.5 lg:shrink-0 lg:gap-3 xl:gap-8" aria-label="Account and offers">
                @foreach ($accountLinks as $link)
                    <a href="{{ $link['url'] }}" title="{{ $link['label'] }}" @class([
                        'group items-center gap-2 rounded-md p-2 transition hover:bg-white/10 xl:p-0 xl:hover:bg-transparent',
                        'flex' => $link['label'] === 'Account',
                        'hidden sm:flex' => $link['label'] !== 'Account',
                    ]) aria-label="{{ $link['label'] }}">
                        <x-store.icon :name="$link['icon']" class="size-5 text-white transition duration-300 ease-spring group-hover:scale-125 lg:size-[22px]" />
                        <span class="hidden leading-tight xl:ml-1.5 xl:block">
                            <span class="block text-[15px] font-medium transition group-hover:text-white/80">{{ $link['label'] }}</span>
                            <span class="mt-0.5 block text-xs text-white/75">{{ $link['caption'] }}</span>
                        </span>
                    </a>
                @endforeach
                <a href="{{ route('store.checkout.show') }}" title="Cart" class="group hidden items-center gap-2 rounded-md p-2 transition hover:bg-white/10 lg:flex xl:p-0 xl:hover:bg-transparent" aria-label="Cart, {{ $cartCount ?? 0 }} {{ Str::plural('item', $cartCount ?? 0) }}">
                    <span class="relative">
                        <x-store.icon name="cart" class="size-5 text-white transition duration-300 ease-spring group-hover:scale-125 lg:size-[22px]" />
                        @if (($cartCount ?? 0) > 0)
                            <span class="absolute -top-2 -right-2 grid h-4 min-w-4 place-items-center rounded-full bg-white px-1 text-[10px] leading-none font-semibold text-accent">{{ $cartCount }}</span>
                        @endif
                    </span>
                    <span class="hidden leading-tight xl:ml-1.5 xl:block">
                        <span class="block text-[15px] font-medium transition group-hover:text-white/80">Cart</span>
                        <span class="mt-0.5 block text-xs text-white/75">{{ $cartCount ?? 0 }} {{ Str::plural('Item', $cartCount ?? 0) }}</span>
                    </span>
                </a>
                @if ($headerSettings['show_pc_builder'] ?? true)
                    <a href="{{ route('store.pc-builder.show') }}" data-ripple class="relative ml-1 hidden overflow-hidden rounded-md bg-white px-3 py-2 text-xs font-semibold whitespace-nowrap text-accent transition hover:-translate-y-0.5 hover:bg-builder hover:text-white hover:shadow-lg hover:shadow-black/30 sm:block lg:px-4 lg:py-2.5 xl:ml-0 xl:px-7 xl:py-3 xl:text-sm">PC Builder</a>
                @endif
            </nav>
        </div>
    </div>

    <nav class="hidden border-b border-gray-200 bg-white shadow-sm lg:block" aria-label="Product categories" data-mega-nav>
        <ul class="mx-auto flex h-11 max-w-hero items-center justify-between gap-x-3 px-4 xl:gap-x-4">
            @foreach (array_slice($navigationLinks, 0, \App\Support\Storefront\NavigationMenu::HEADER_LIMIT) as $link)
                <li class="group/nav relative flex h-full shrink-0 items-center" @if ($link['children'] !== []) data-mega-item @endif>
                    <a href="{{ $link['url'] }}" @if ($link['children'] !== []) aria-haspopup="true" aria-expanded="false" @endif class="nav-underline text-[12px] font-medium whitespace-nowrap xl:text-[13px] 2xl:text-sm text-gray-900 transition hover:text-accent">{{ $link['label'] }}</a>

                    @if ($link['children'] !== [])
                        <x-store.mega-menu :items="$link['children']" :align-right="$loop->remaining < 4" />
                    @endif
                </li>
            @endforeach
        </ul>
    </nav>
</header>

@include('store.partials.mobile-drawer', ['accountLinks' => $accountLinks])
