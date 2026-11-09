<dialog id="mobile-menu" class="drawer bg-white p-0 text-gray-900 shadow-2xl lg:hidden" aria-label="{{ __('Main menu') }}">
    <div class="flex h-full flex-col">
        <div class="flex items-center justify-between bg-topbar px-4 py-3">
            <x-store.logo class="h-9 w-auto" />
            <button type="button" data-drawer-close class="grid size-10 cursor-pointer place-items-center rounded-md text-white transition hover:bg-white/10 hover:rotate-90 active:scale-90" aria-label="{{ __('Close menu') }}">
                <x-store.icon name="x" class="size-6" />
            </button>
        </div>

        <div class="flex-1 overflow-y-auto overscroll-contain">
            @if ($showLanguageSwitcher ?? true)
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-4 pt-4 pb-3">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold tracking-wider text-gray-500 uppercase">
                        <x-store.icon name="globe" class="size-4" />
                        {{ __('Language') }}
                    </span>
                    <x-store.language-switcher variant="light" labels="name" class="w-44" />
                </div>
            @endif
            <ul class="grid grid-cols-2 gap-2 border-b border-gray-100 p-4">
                @foreach ($accountLinks as $link)
                    <li data-drawer-item style="--i: {{ $loop->index }}">
                        <a href="{{ $link['url'] }}" @class([
                            'flex items-center gap-2 rounded-lg px-3 py-3 text-xs font-medium transition active:scale-95',
                            'bg-accent/10 text-accent' => $link['active'],
                            'bg-surface' => ! $link['active'],
                        ]) @if ($link['active']) aria-current="page" @endif>
                            <x-store.icon :name="$link['icon']" class="size-5 shrink-0 text-accent" />
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <p class="px-4 pt-4 pb-1 text-xs font-semibold tracking-wider text-gray-500 uppercase">{{ __('Categories') }}</p>
            <ul class="px-2 pb-4">
                <li data-drawer-item style="--i: {{ count($accountLinks) }}">
                    <a href="{{ route('store.trending') }}" class="group flex items-center justify-between rounded-md px-3 py-3 text-[15px] font-semibold text-accent transition hover:bg-surface active:bg-surface" @if (request()->routeIs('store.trending')) aria-current="page" @endif>
                        <span class="inline-flex items-center gap-2"><x-store.icon name="bolt" class="size-4" /> {{ __('Trending') }}</span>
                        <x-store.icon name="chevron-right" class="size-4 transition group-hover:translate-x-1" />
                    </a>
                </li>
                @foreach ($navigationLinks as $link)
                    <li data-drawer-item style="--i: {{ $loop->index + count($accountLinks) + 1 }}">
                        <a href="{{ $link['url'] }}" class="group flex items-center justify-between rounded-md px-3 py-3 text-[15px] font-medium transition hover:bg-surface hover:text-accent active:bg-surface">
                            {{ $link['label'] }}
                            <x-store.icon name="chevron-right" class="size-4 text-gray-400 transition group-hover:translate-x-1 group-hover:text-accent" />
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        @if ($headerSettings['show_pc_builder'] ?? false)
            <div class="border-t border-gray-100 p-4 pb-[calc(1rem+env(safe-area-inset-bottom))]">
                <a href="{{ route('store.pc-builder.show') }}" data-ripple class="relative flex h-11 items-center justify-center gap-2 overflow-hidden rounded-md bg-button text-sm font-semibold text-button-text transition hover:bg-button-hover active:scale-[0.98]">
                    <x-store.icon name="cpu" class="size-5" /> {{ __('PC Builder') }}
                </a>
            </div>
        @endif
    </div>
</dialog>
