<section class="border-t border-gray-200/70 bg-surface">
    <div class="mx-auto max-w-hero px-4 py-7 lg:px-3 lg:py-8">
        <x-store.section-heading :title="$section['title'] ?? __('Featured Brands')" :subtitle="$section['subtitle']" />

        <ul class="mt-5 grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-6" data-reveal-group>
            @foreach ($section['brands'] as $brand)
                <li data-reveal>
                    <a href="{{ $brand['url'] }}" class="grid h-20 place-items-center rounded-md border border-gray-200 bg-white p-3 transition hover:-translate-y-1 hover:border-accent/50 hover:shadow-md">
                        @if ($brand['image'])
                            <img src="{{ $brand['image'] }}" alt="{{ $brand['name'] }}" width="120" height="48" loading="lazy" class="max-h-12 w-auto object-contain">
                        @else
                            <span class="text-sm font-semibold text-gray-800">{{ $brand['name'] }}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>
