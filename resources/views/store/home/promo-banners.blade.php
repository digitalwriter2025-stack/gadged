<section class="bg-surface">
    <div @class(['mx-auto grid max-w-hero gap-3 px-4 pb-2 lg:px-3', 'md:grid-cols-2' => count($section['banners']) > 1])>
        @foreach ($section['banners'] as $banner)
            <a href="{{ $banner['url'] }}" @if ($banner['new_tab']) target="_blank" rel="noopener" @endif class="group relative block overflow-hidden rounded-lg shadow-sm transition duration-500 hover:shadow-xl" data-reveal>
                <picture>
                    @if ($banner['mobile_image'])
                        <source media="(max-width: 767px)" srcset="{{ $banner['mobile_image'] }}">
                    @endif
                    <img src="{{ $banner['image'] }}" alt="{{ $banner['alt'] }}" width="1600" height="603" loading="lazy" class="h-auto w-full transition duration-700 ease-out-expo group-hover:scale-[1.03]">
                </picture>
                <span class="absolute inset-0 -translate-x-full bg-linear-to-r from-transparent via-white/25 to-transparent transition duration-1000 ease-out-expo group-hover:translate-x-full" aria-hidden="true"></span>
            </a>
        @endforeach
    </div>
</section>
