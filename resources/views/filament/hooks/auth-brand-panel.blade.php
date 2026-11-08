@php
    $settings = app(\App\Services\SettingsService::class);
    $siteName = (string) $settings->get('general.site_name', config('app.name'));
    $logoPath = $settings->get('general.logo_dark') ?: $settings->get('general.logo');
    $logoUrl = is_string($logoPath) && $logoPath !== '' ? \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath) : null;

    $features = [
        ['icon' => 'heroicon-o-shopping-bag', 'title' => 'Orders & checkout', 'text' => 'Track every order from placement to delivery.'],
        ['icon' => 'heroicon-o-cube', 'title' => 'Catalog & inventory', 'text' => 'Keep products, variants and stock in sync.'],
        ['icon' => 'heroicon-o-chart-bar', 'title' => 'Sales insights', 'text' => 'See what sells and grow with real numbers.'],
    ];
@endphp

<aside class="gx-auth-brand" aria-hidden="true">
    <div class="gx-auth-brand-glow"></div>
    <div class="gx-auth-brand-grid"></div>

    <div class="gx-auth-brand-inner">
        <div class="gx-auth-brand-logo">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="" />
            @else
                <span class="gx-auth-brand-monogram">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($siteName, 0, 1)) }}</span>
            @endif
            <span class="gx-auth-brand-name">{{ $siteName }}</span>
        </div>

        <div class="gx-auth-brand-copy">
            <span class="gx-auth-brand-badge">
                <x-filament::icon icon="heroicon-m-shield-check" class="gx-auth-brand-badge-icon" />
                Admin control center
            </span>

            <h2>Run your whole store<br />from one place.</h2>

            <p>Orders, products, customers and marketing — managed in a single, secure dashboard.</p>

            <ul class="gx-auth-brand-features">
                @foreach ($features as $feature)
                    <li>
                        <span class="gx-auth-brand-feature-icon">
                            <x-filament::icon :icon="$feature['icon']" />
                        </span>
                        <span>
                            <strong>{{ $feature['title'] }}</strong>
                            <small>{{ $feature['text'] }}</small>
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>

        <p class="gx-auth-brand-footer">&copy; {{ now()->year }} {{ $siteName }}. All rights reserved.</p>
    </div>
</aside>
