@props(['title', 'subtitle', 'products', 'flashSale' => false, 'viewAllUrl' => '#'])

<section {{ $attributes->merge(['class' => 'bg-surface']) }} data-carousel>
    <div class="mx-auto max-w-hero px-4 py-7 lg:px-3 lg:py-8">
        <div class="flex items-center justify-between gap-3 sm:items-end">
            <x-store.section-heading :title="$title" :subtitle="$subtitle" />

            <div class="flex shrink-0 items-center gap-2" data-reveal>
                <button type="button" data-carousel-prev class="hidden size-9 cursor-pointer place-items-center rounded-full border border-gray-200 bg-white text-gray-700 transition hover:border-button hover:bg-button hover:text-button-text active:scale-90 disabled:pointer-events-none disabled:opacity-40 sm:grid lg:size-8" aria-label="{{ __('Previous: :title', ['title' => $title]) }}">
                    <x-store.icon name="chevron-left" class="size-4" />
                </button>
                <button type="button" data-carousel-next class="hidden size-9 cursor-pointer place-items-center rounded-full border border-gray-200 bg-white text-gray-700 transition hover:border-button hover:bg-button hover:text-button-text active:scale-90 disabled:pointer-events-none disabled:opacity-40 sm:grid lg:size-8" aria-label="{{ __('Next: :title', ['title' => $title]) }}">
                    <x-store.icon name="chevron-right" class="size-4" />
                </button>
                <a href="{{ $viewAllUrl }}" class="group flex h-8 items-center gap-1 rounded-full border border-gray-200 bg-white px-3 text-[11px] sm:h-9 sm:px-3.5 sm:text-xs font-medium text-gray-800 transition hover:border-accent hover:text-accent lg:h-8 lg:text-[11px]">
                    {{ __('View All') }} <x-store.icon name="arrow-right" class="size-3 transition group-hover:translate-x-1" />
                </a>
            </div>
        </div>

        <div data-carousel-viewport class="-mx-4 mt-4 px-4 lg:-mx-1 lg:px-1">
            <ul data-carousel-track data-reveal-group class="swiper-wrapper scrollbar-none -mx-4 flex items-stretch snap-x snap-mandatory scroll-px-4 gap-3 overflow-x-auto scroll-smooth px-4 pt-2 pb-10 -mb-6 lg:-mx-1 lg:scroll-px-1 lg:px-1">
                @foreach ($products as $product)
                    <li data-reveal class="swiper-slide flex flex-col h-auto w-[58%] shrink-0 min-[360px]:w-[44%] snap-start sm:w-[calc((100%-1.5rem)/3)] md:w-[calc((100%-2.25rem)/4)] lg:w-[calc((100%-3rem)/5)]">
                        <x-store.product-card :product="$product" :flash-sale="$flashSale" />
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
