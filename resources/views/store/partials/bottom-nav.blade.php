<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-gray-200 bg-white/95 pb-[env(safe-area-inset-bottom)] shadow-[0_-4px_16px_rgba(15,23,42,0.08)] backdrop-blur lg:hidden" aria-label="Quick navigation">
    <ul class="mx-auto grid h-15 max-w-md grid-cols-4">
        <li>
            <a href="{{ route('store.home') }}" @class([
                'group flex h-full flex-col items-center justify-center gap-0.5 text-[11px] font-medium transition active:scale-90',
                'text-accent' => request()->routeIs('store.home'),
                'text-gray-600' => ! request()->routeIs('store.home'),
            ]) @if (request()->routeIs('store.home')) aria-current="page" @endif>
                <x-store.icon name="home" class="size-6 transition duration-300 ease-spring group-hover:-translate-y-0.5" />
                Home
            </a>
        </li>
        <li>
            <button type="button" data-drawer-open="mobile-menu" aria-controls="mobile-menu" aria-expanded="false" class="group flex h-full w-full cursor-pointer flex-col items-center justify-center gap-0.5 text-[11px] font-medium text-gray-600 transition active:scale-90">
                <x-store.icon name="grid" class="size-6 transition duration-300 ease-spring group-hover:-translate-y-0.5" />
                Categories
            </button>
        </li>
        <li>
            <a href="{{ route('store.checkout.show') }}" @class([
                'group flex h-full flex-col items-center justify-center gap-0.5 text-[11px] font-medium transition active:scale-90',
                'text-accent' => request()->routeIs('store.checkout.*'),
                'text-gray-600' => ! request()->routeIs('store.checkout.*'),
            ]) @if (request()->routeIs('store.checkout.show')) aria-current="page" @endif>
                <span class="relative">
                    <x-store.icon name="cart" class="size-6 transition duration-300 ease-spring group-hover:-translate-y-0.5" />
                    @if (($cartCount ?? 0) > 0)
                        <span class="absolute -top-1.5 -right-2 grid h-4 min-w-4 place-items-center rounded-full bg-accent px-1 text-[10px] leading-none font-semibold text-white">{{ $cartCount }}<span class="sr-only"> items</span></span>
                    @endif
                </span>
                Cart
            </a>
        </li>
        <li>
            <a href="#" class="group flex h-full flex-col items-center justify-center gap-0.5 text-[11px] font-medium text-gray-600 transition active:scale-90">
                <x-store.icon name="user" class="size-6 transition duration-300 ease-spring group-hover:-translate-y-0.5" />
                Account
            </a>
        </li>
    </ul>
</nav>
