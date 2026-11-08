<dialog id="mobile-menu" class="drawer bg-white p-0 text-gray-900 shadow-2xl lg:hidden" aria-label="Main menu">
    <div class="flex h-full flex-col">
        <div class="flex items-center justify-between bg-topbar px-4 py-3">
            <x-store.logo class="h-9 w-auto" />
            <button type="button" data-drawer-close class="grid size-10 cursor-pointer place-items-center rounded-md text-white transition hover:bg-white/10 hover:rotate-90 active:scale-90" aria-label="Close menu">
                <x-store.icon name="x" class="size-6" />
            </button>
        </div>

        <div class="flex-1 overflow-y-auto overscroll-contain">
            <ul class="grid grid-cols-3 gap-2 border-b border-gray-100 p-4">
                @foreach ($accountLinks as $link)
                    <li data-drawer-item style="--i: {{ $loop->index }}">
                        <a href="{{ $link['url'] }}" class="flex flex-col items-center gap-1.5 rounded-lg bg-surface px-2 py-3 text-center text-xs font-medium transition active:scale-95">
                            <x-store.icon :name="$link['icon']" class="size-5 text-accent" />
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <p class="px-4 pt-4 pb-1 text-xs font-semibold tracking-wider text-gray-500 uppercase">Categories</p>
            <ul class="px-2 pb-4">
                @foreach ($navigationLinks as $link)
                    <li data-drawer-item style="--i: {{ $loop->index + 3 }}">
                        <a href="{{ $link['url'] }}" class="group flex items-center justify-between rounded-md px-3 py-3 text-[15px] font-medium transition hover:bg-surface hover:text-accent active:bg-surface">
                            {{ $link['label'] }}
                            <x-store.icon name="chevron-right" class="size-4 text-gray-400 transition group-hover:translate-x-1 group-hover:text-accent" />
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="border-t border-gray-100 p-4 pb-[calc(1rem+env(safe-area-inset-bottom))]">
            <a href="#" data-ripple class="relative flex h-11 items-center justify-center gap-2 overflow-hidden rounded-md bg-builder text-sm font-semibold text-white transition active:scale-[0.98]">
                <x-store.icon name="cpu" class="size-5" /> PC Builder
            </a>
        </div>
    </div>
</dialog>
