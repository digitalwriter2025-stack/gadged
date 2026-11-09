<span class="gx-hl-badge">
    <span class="gx-hl-icon">{!! \App\Support\Icons\IconHelper::render($icon) !!}</span>
    <span>
        <span class="gx-cell-title">{{ $title }}</span>
        @if (filled($subtitle))
            <span class="gx-cell-meta">{{ $subtitle }}</span>
        @endif
    </span>
</span>
