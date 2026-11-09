<span class="gx-hs-block">
    <span class="gx-hs-icon fi-color-{{ $type->getColor() }}">{{ \Filament\Support\generate_icon_html($type->getIcon()) }}</span>
    <span>
        <span class="gx-hs-heading">
            <span class="gx-cell-title">{{ $title }}</span>
            @if ($isHidden)
                <span class="gx-hs-hidden-tag">
                    {{ \Filament\Support\generate_icon_html(\Filament\Support\Icons\Heroicon::EyeSlash, size: \Filament\Support\Enums\IconSize::Small) }}
                    Hidden from homepage
                </span>
            @endif
        </span>
        <span class="gx-cell-meta">{{ $summary }}</span>
    </span>
</span>
