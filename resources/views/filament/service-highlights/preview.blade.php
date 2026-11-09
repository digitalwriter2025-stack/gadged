<div class="gx-hl-preview">
    <div class="gx-hl-preview-strip">
        @include('filament.service-highlights.badge', ['icon' => $icon, 'title' => filled($title) ? $title : 'Badge title', 'subtitle' => $subtitle])

        @if (filled($titleBn))
            @include('filament.service-highlights.badge', ['icon' => $icon, 'title' => $titleBn, 'subtitle' => filled($subtitleBn) ? $subtitleBn : $subtitle])
        @endif
    </div>

    <p class="gx-hl-preview-note">How the badge looks in the strip under the homepage slider.</p>
</div>
