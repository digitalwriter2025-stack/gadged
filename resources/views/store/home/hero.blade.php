{{-- Hero slider (banners placed "hero_main") with the two side banners. --}}
<section class="bg-hero">
    <div class="mx-auto max-w-hero px-3 pt-2.5 sm:px-4 lg:px-3">
        <div @class(['grid gap-1.5 lg:gap-2', 'lg:grid-cols-[840fr_418fr]' => $section['slides'] !== [] && $section['side'] !== []])>
            @if ($section['slides'] !== [])
                <div class="group/hero relative animate-fade-up lg:h-full" data-hero-slider aria-roledescription="carousel" aria-label="Featured offers">
                    <div data-hero-viewport class="relative aspect-[1672/941] touch-pan-y overflow-hidden rounded-md lg:aspect-auto lg:h-full lg:rounded-none">
                        <div data-hero-track class="swiper-wrapper absolute inset-0">
                            @foreach ($section['slides'] as $slide)
                                <a href="{{ $slide['url'] }}" @if ($slide['new_tab']) target="_blank" rel="noopener" @endif data-slide aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $loop->count }}" @class(['swiper-slide hero-slide absolute inset-0 overflow-hidden transition-opacity duration-700', 'opacity-100' => $loop->first, 'opacity-0' => ! $loop->first])>
                                    <picture>
                                        @if ($slide['mobile_image'])
                                            <source media="(max-width: 767px)" srcset="{{ $slide['mobile_image'] }}">
                                        @endif
                                        <img src="{{ $slide['image'] }}" alt="{{ $slide['alt'] }}" width="1672" height="941" draggable="false" class="size-full object-cover" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                                    </picture>
                                </a>
                            @endforeach
                        </div>

                        @if (count($section['slides']) > 1)
                            @foreach (['prev' => 'chevron-left', 'next' => 'chevron-right'] as $direction => $icon)
                                <button type="button" data-slide-{{ $direction }} @class([
                                    'absolute top-1/2 z-10 hidden size-10 -translate-y-1/2 cursor-pointer place-items-center rounded-full bg-white/85 text-gray-900 opacity-0 shadow-lg backdrop-blur transition duration-300 ease-out-expo group-hover/hero:opacity-100 hover:scale-110 hover:bg-white focus-visible:opacity-100 active:scale-95 md:grid',
                                    'left-3 group-hover/hero:left-4' => $direction === 'prev',
                                    'right-3 group-hover/hero:right-4' => $direction === 'next',
                                ]) aria-label="{{ $direction === 'prev' ? 'Previous' : 'Next' }} slide">
                                    <x-store.icon :name="$icon" class="size-5" />
                                </button>
                            @endforeach
                        @endif
                    </div>

                    @if (count($section['slides']) > 1)
                        <div class="flex justify-center gap-1.5 py-2 lg:absolute lg:inset-x-0 lg:bottom-0 lg:z-10 lg:translate-y-1/2 lg:py-0">
                            @foreach ($section['slides'] as $slide)
                                <button type="button" data-slide-dot @class(['hero-dot group/dot grid h-4 w-7 cursor-pointer place-items-center', 'is-active' => $loop->first]) aria-label="Show slide {{ $loop->iteration }}" @if ($loop->first) aria-current="true" @endif>
                                    <span class="relative block h-[3px] w-full overflow-hidden rounded-full bg-gray-300 transition group-hover/dot:bg-gray-400">
                                        <span class="hero-dot-fill absolute inset-0 rounded-full bg-gray-800"></span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            @if ($section['side'] !== [])
                <div class="grid grid-cols-2 gap-1.5 lg:grid-cols-1 lg:grid-rows-2 lg:gap-2">
                    @foreach ($section['side'] as $banner)
                        <a href="{{ $banner['url'] }}" @if ($banner['new_tab']) target="_blank" rel="noopener" @endif class="group/banner relative block animate-fade-up overflow-hidden rounded-md lg:rounded-none" style="animation-delay: {{ 150 + $loop->index * 120 }}ms">
                            <img src="{{ $banner['image'] }}" alt="{{ $banner['alt'] }}" width="418" height="216" data-parallax="4" class="aspect-[418/216] size-full object-cover object-right transition duration-700 ease-out-expo group-hover/banner:scale-105">
                            <span class="absolute inset-0 -translate-x-full bg-linear-to-r from-transparent via-white/25 to-transparent transition duration-700 ease-out-expo group-hover/banner:translate-x-full" aria-hidden="true"></span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
