@props(['items', 'container' => 'max-w-store'])

@php
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => collect($items)->values()->map(fn (array $crumb, int $index): array => array_filter([
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $crumb['label'],
            'item' => $crumb['url'],
        ]))->all(),
    ];
@endphp

<nav {{ $attributes->merge(['class' => 'border-b border-gray-200 bg-white']) }} aria-label="Breadcrumb">
    <ol class="scrollbar-none mx-auto flex {{ $container }} items-center gap-1.5 overflow-x-auto px-4 py-2.5 text-[11px] whitespace-nowrap text-gray-500 lg:text-xs">
        @foreach ($items as $crumb)
            <li class="flex items-center gap-1.5">
                @unless ($loop->first)
                    <x-store.icon name="chevron-right" class="size-3 text-gray-400" />
                @endunless
                @if ($crumb['url'])
                    <a href="{{ $crumb['url'] }}" class="transition hover:text-accent">{{ $crumb['label'] }}</a>
                @else
                    <span class="font-medium text-gray-800" aria-current="page">{{ $crumb['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
    <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
</nav>
