<x-filament-widgets::widget>
    <div class="gx-welcome">
        <div class="gx-welcome-glow" aria-hidden="true"></div>

        <div class="gx-welcome-copy">
            <span class="gx-welcome-date">
                <x-filament::icon icon="heroicon-m-calendar-days" />
                {{ $this->getToday() }}
            </span>

            <h2 class="gx-welcome-title">
                {{ $this->getGreeting() }}, <span>{{ auth()->user()?->name }}</span>
            </h2>

            <p class="gx-welcome-text">Here's how your store is doing today.</p>
        </div>

        <nav class="gx-welcome-actions" aria-label="Quick actions">
            @foreach ($this->getQuickActions() as $action)
                <a
                    href="{{ $action['url'] }}"
                    wire:navigate
                    @class(['gx-welcome-action', 'gx-welcome-action-primary' => $action['primary']])
                >
                    <x-filament::icon :icon="$action['icon']" />
                    {{ $action['label'] }}
                </a>
            @endforeach

            <a href="{{ route('store.home') }}" target="_blank" rel="noopener" class="gx-welcome-action">
                <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" />
                Visit store
            </a>
        </nav>
    </div>
</x-filament-widgets::widget>
