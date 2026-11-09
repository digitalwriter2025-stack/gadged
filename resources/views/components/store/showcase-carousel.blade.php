@props([
    'title',
    'products',
    'theme' => 'aurora',
    'eyebrow' => null,
    'ctaLabel' => null,
    'ctaUrl' => '#',
    'images' => [],
])

@php
    $isMidnight = $theme === 'midnight';
    $palette = match ($theme) {
        'midnight' => 'bg-black',
        default => 'bg-linear-to-br from-[#5c0000] via-[#a30000] to-[#df0100]',
    };
    $hasEyebrow = filled($eyebrow);
    $collage = array_slice($images, 0, 2);
    $hasCollage = filled($collage);
    $ctaLabel ??= __('Shop now');
    $headline = preg_replace('/(৳[\d০-৯,]+)/u', '<span class="'.($isMidnight ? 'text-[#ff5a52]' : 'text-yellow-300').'">$1</span>', e($title));
@endphp

<section {{ $attributes->merge(['class' => 'bg-surface']) }}>
    <div class="mx-auto max-w-hero px-4 py-4 lg:px-3 lg:py-5">
        <div class="group/showcase relative isolate overflow-hidden rounded-2xl {{ $palette }} lg:flex" data-carousel="showcase" data-reveal>
            @if ($isMidnight)
                <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(110%_60%_at_0%_0%,rgba(234,88,12,0.5),rgba(124,45,18,0.25)_45%,transparent_75%)] lg:bg-[radial-gradient(55%_120%_at_0%_100%,rgba(234,88,12,0.8),rgba(154,52,18,0.5)_30%,rgba(67,20,7,0.3)_55%,transparent_75%)]" aria-hidden="true"></div>
            @else
                <div class="pointer-events-none absolute -top-24 -left-20 -z-10 size-80 rounded-full bg-white/15 blur-3xl" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -right-16 -bottom-28 -z-10 size-96 rounded-full bg-fuchsia-400/25 blur-3xl" aria-hidden="true"></div>
            @endif

            <div @class([
                'relative flex flex-col gap-6 p-6 text-white lg:w-[32%] lg:shrink-0 lg:py-8 lg:pr-5 lg:pl-8',
                'md:flex-row md:items-center md:justify-between lg:flex-col lg:items-stretch lg:justify-start' => $hasCollage,
            ])>
                <div @class(['flex flex-col items-start gap-5', 'lg:flex-1' => ! $hasCollage])>
                    <div>
                        <h2 @class([
                            'font-extrabold tracking-tight text-balance',
                            'text-4xl leading-[1.05] lg:text-[44px]' => $hasEyebrow,
                            'max-w-md text-[22px] leading-tight lg:text-[28px] lg:leading-[1.2]' => ! $hasEyebrow,
                        ])>{!! $headline !!}</h2>

                        @if ($hasEyebrow)
                            <p class="mt-3 max-w-sm text-sm leading-relaxed text-white/80 lg:text-[15px]">{{ $eyebrow }}</p>
                        @endif
                    </div>

                    <a href="{{ $ctaUrl }}" @class([
                        'group/cta inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-bold text-gray-900 shadow-lg shadow-black/25 transition duration-300 hover:gap-3 hover:shadow-xl focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white',
                        'hover:bg-red-50' => $isMidnight,
                        'hover:bg-red-50 lg:mt-auto' => ! $isMidnight,
                    ])>
                        {{ $ctaLabel }}
                        <x-store.icon name="arrow-right" class="size-4 transition group-hover/cta:translate-x-0.5" />
                    </a>
                </div>

                @if ($hasCollage)
                    <div class="pointer-events-none relative hidden h-36 w-60 shrink-0 md:block lg:mt-auto lg:h-48 lg:w-full" aria-hidden="true">
                        <div class="absolute inset-x-8 bottom-0 h-3/4 rounded-full bg-[#df0100]/40 blur-3xl"></div>
                        @foreach ($collage as $image)
                            <img src="{{ asset($image) }}" alt="" width="200" height="200" loading="lazy" @class([
                                'absolute w-[46%] rounded-2xl bg-white p-2 shadow-[0_20px_40px_-12px_rgba(0,0,0,0.85)] ring-1 ring-white/10 transition duration-700 ease-out-expo',
                                'top-0 left-0 -rotate-6 group-hover/showcase:-translate-x-1 group-hover/showcase:-rotate-9' => $loop->first,
                                'right-0 bottom-0 rotate-6 group-hover/showcase:translate-x-1 group-hover/showcase:rotate-9' => ! $loop->first,
                            ])>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="@container relative min-w-0 flex-1 pb-6 lg:mr-8 lg:self-center lg:py-8">
                <div class="relative">
                    <div data-carousel-viewport class="overflow-hidden px-4 py-2 lg:px-0">
                        <ul data-carousel-track class="swiper-wrapper scrollbar-none flex items-stretch snap-x snap-mandatory gap-3 overflow-x-auto">
                            @foreach ($products as $product)
                                <li class="swiper-slide flex flex-col h-auto w-[calc((100%-7.2px)/1.6)] shrink-0 snap-start @min-[300px]:w-[calc((100%-13.2px)/2.1)] @md:w-[calc((100%-26.4px)/3.2)] @xl:w-[calc((100%-31.2px)/3.6)] @3xl:w-[calc((100%-39.6px)/4.3)]">
                                    <x-store.showcase-card :product="$product" />
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <button type="button" data-carousel-prev class="absolute top-1/2 left-2 z-20 hidden size-11 -translate-y-1/2 cursor-pointer place-items-center rounded-full bg-white text-gray-900 shadow-[0_6px_20px_rgba(0,0,0,0.35)] ring-1 ring-black/5 transition hover:scale-105 active:scale-95 disabled:pointer-events-none disabled:opacity-0 sm:grid lg:left-0 lg:size-12 lg:-translate-x-1/2" aria-label="{{ __('Previous: :title', ['title' => $title]) }}">
                        <x-store.icon name="chevron-left" class="size-5" />
                    </button>
                    <button type="button" data-carousel-next class="absolute top-1/2 right-2 z-20 hidden size-11 -translate-y-1/2 cursor-pointer place-items-center rounded-full bg-white text-gray-900 shadow-[0_6px_20px_rgba(0,0,0,0.35)] ring-1 ring-black/5 transition hover:scale-105 active:scale-95 disabled:pointer-events-none disabled:opacity-0 sm:grid lg:right-0 lg:size-12 lg:translate-x-1/2" aria-label="{{ __('Next: :title', ['title' => $title]) }}">
                        <x-store.icon name="chevron-right" class="size-5" />
                    </button>
                </div>

                <div data-carousel-scrollbar hidden class="mx-4 mt-3 h-1 cursor-pointer rounded-full bg-white/15 lg:mx-0 lg:mt-4">
                    <div class="swiper-scrollbar-drag h-full rounded-full bg-white/80"></div>
                </div>
            </div>
        </div>
    </div>
</section>
