<x-filament-panels::page>
    <div class="gx-cache">
        <div class="gx-cache-stats">
            <div class="gx-cache-stat gx-tone-info">
                <span class="gx-cache-stat-icon"><x-filament::icon icon="heroicon-o-circle-stack" /></span>
                <div>
                    <div class="gx-cache-stat-label">Cache Driver</div>
                    <div class="gx-cache-stat-value">{{ str(config('cache.default', 'file'))->upper() }}</div>
                </div>
            </div>

            <div class="gx-cache-stat gx-tone-success">
                <span class="gx-cache-stat-icon"><x-filament::icon icon="heroicon-o-check-circle" /></span>
                <div>
                    <div class="gx-cache-stat-label">Cache Status</div>
                    <div class="gx-cache-stat-value gx-cache-stat-value-ok">Operational</div>
                </div>
            </div>

            <div class="gx-cache-stat gx-tone-gray">
                <span class="gx-cache-stat-icon"><x-filament::icon icon="heroicon-o-clock" /></span>
                <div>
                    <div class="gx-cache-stat-label">Server Time</div>
                    <div class="gx-cache-stat-value gx-cache-stat-value-sm">{{ now(config('app.display_timezone'))->format('H:i:s, d M Y') }}</div>
                </div>
            </div>

            <div class="gx-cache-stat gx-tone-warning">
                <span class="gx-cache-stat-icon"><x-filament::icon icon="heroicon-o-bolt" /></span>
                <div>
                    <div class="gx-cache-stat-label">Auto Invalidation</div>
                    <div class="gx-cache-stat-value gx-cache-stat-value-sm">Active (Model Hooks)</div>
                </div>
            </div>
        </div>

        <div class="gx-cache-hero">
            <div>
                <h3 class="gx-cache-hero-title">Purge All System & Store Caches</h3>
                <p class="gx-cache-hero-text">Flushes all product catalog cards, homepage sections, navigation menus, store settings, views, and compiled route caches in one click.</p>
            </div>

            <x-filament::button
                wire:click="clearAllCache"
                color="danger"
                icon="heroicon-m-trash"
                size="lg"
                class="gx-cache-hero-button"
            >
                Flush All Caches
            </x-filament::button>
        </div>

        <div class="gx-cache-targets">
            @foreach ($this->getCacheTargets() as $target)
                <div class="gx-cache-target gx-tone-{{ $target['tone'] }}">
                    <div class="gx-cache-target-head">
                        <span class="gx-cache-target-icon"><x-filament::icon :icon="$target['icon']" /></span>
                        <h4 class="gx-cache-target-title">{{ $target['title'] }}</h4>
                        <span class="gx-cache-target-tag">{{ $target['tag'] }}</span>
                    </div>

                    <p class="gx-cache-target-text">{{ $target['description'] }}</p>

                    <div class="gx-cache-target-footer">
                        <x-filament::button
                            wire:click="{{ $target['action'] }}"
                            color="gray"
                            outlined
                            icon="heroicon-m-arrow-path"
                            class="gx-cache-target-button"
                        >
                            {{ $target['label'] }}
                        </x-filament::button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
