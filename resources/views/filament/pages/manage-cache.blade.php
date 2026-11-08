<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Status Overview Cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="grid size-10 place-items-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Cache Driver</div>
                        <div class="text-lg font-bold text-gray-900 dark:text-white uppercase">{{ config('cache.default', 'file') }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="grid size-10 place-items-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Cache Status</div>
                        <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">Operational</div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="grid size-10 place-items-center rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Server Time</div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ now()->format('H:i:s d M Y') }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="grid size-10 place-items-center rounded-lg bg-orange-50 text-orange-600 dark:bg-orange-950/50 dark:text-orange-400">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Auto Invalidation</div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">Active (Model Hooks)</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Master Action Card --}}
        <div class="rounded-2xl border border-primary-500/20 bg-linear-to-r from-primary-500/10 via-primary-500/5 to-transparent p-6 dark:from-primary-950/30">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Purge All System & Store Caches</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Flushes all product catalog cards, homepage sections, navigation menus, store settings, views, and compiled route caches in one click.</p>
                </div>
                <button
                    wire:click="clearAllCache"
                    wire:loading.attr="disabled"
                    type="button"
                    class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 active:scale-95 disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="clearAllCache" class="flex items-center gap-2">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Flush All Caches
                    </span>
                    <span wire:loading wire:target="clearAllCache" class="flex items-center gap-2">
                        <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Flushing...
                    </span>
                </button>
            </div>
        </div>

        {{-- Selective Cache Controls --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            {{-- Catalog Cache Card --}}
            <div class="flex flex-col justify-between rounded-xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div>
                    <div class="flex items-center justify-between">
                        <h4 class="font-semibold text-gray-900 dark:text-white">Storefront Catalog Cache</h4>
                        <span class="rounded-md bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">CatalogCache</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Clears cached homepage sections, banners, category trees, product cards, and header mega-menus.</p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button
                        wire:click="flushCatalogCache"
                        wire:loading.attr="disabled"
                        type="button"
                        class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <span wire:loading.remove wire:target="flushCatalogCache">Clear Catalog Cache</span>
                        <span wire:loading wire:target="flushCatalogCache">Clearing...</span>
                    </button>
                </div>
            </div>

            {{-- Settings Cache Card --}}
            <div class="flex flex-col justify-between rounded-xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div>
                    <div class="flex items-center justify-between">
                        <h4 class="font-semibold text-gray-900 dark:text-white">Store Settings Cache</h4>
                        <span class="rounded-md bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">SettingsService</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Clears cached site identity, logos, contact numbers, social media links, SEO meta tags, and footer information.</p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button
                        wire:click="flushSettingsCache"
                        wire:loading.attr="disabled"
                        type="button"
                        class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <span wire:loading.remove wire:target="flushSettingsCache">Clear Settings Cache</span>
                        <span wire:loading wire:target="flushSettingsCache">Clearing...</span>
                    </button>
                </div>
            </div>

            {{-- Blade Views Cache Card --}}
            <div class="flex flex-col justify-between rounded-xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div>
                    <div class="flex items-center justify-between">
                        <h4 class="font-semibold text-gray-900 dark:text-white">Compiled Blade Templates</h4>
                        <span class="rounded-md bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">view:clear</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Flushes all compiled Blade templates. Use when template layout changes or icon changes aren't immediately appearing.</p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button
                        wire:click="clearViewCache"
                        wire:loading.attr="disabled"
                        type="button"
                        class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <span wire:loading.remove wire:target="clearViewCache">Clear View Cache</span>
                        <span wire:loading wire:target="clearViewCache">Clearing...</span>
                    </button>
                </div>
            </div>

            {{-- Route & Config Cache Card --}}
            <div class="flex flex-col justify-between rounded-xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div>
                    <div class="flex items-center justify-between">
                        <h4 class="font-semibold text-gray-900 dark:text-white">Route & Config Caches</h4>
                        <span class="rounded-md bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-700 dark:bg-purple-900/50 dark:text-purple-300">route & config</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Clears cached application configuration files and registered URL routes. Useful after updating environment or routes.</p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button
                        wire:click="clearRouteConfigCache"
                        wire:loading.attr="disabled"
                        type="button"
                        class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <span wire:loading.remove wire:target="clearRouteConfigCache">Clear Route & Config Cache</span>
                        <span wire:loading wire:target="clearRouteConfigCache">Clearing...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
