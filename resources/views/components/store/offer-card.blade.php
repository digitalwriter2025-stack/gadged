@props(['offer'])

<article class="group flex flex-col overflow-hidden rounded-lg bg-white p-3 shadow-[0_1px_4px_rgba(0,0,0,0.08)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_10px_24px_rgba(0,0,0,0.12)] lg:p-4" data-reveal>
    <a href="{{ $offer['url'] }}" class="block aspect-square overflow-hidden rounded-md bg-white" tabindex="-1" aria-hidden="true">
        @if ($offer['image'])
            <img src="{{ $offer['image'] }}" alt="" width="480" height="480" loading="lazy" decoding="async" class="size-full {{ $offer['image_fit'] }} transition duration-500 group-hover:scale-105">
        @else
            <span class="flex size-full flex-col items-center justify-center gap-3 p-6 text-center text-white transition duration-500 group-hover:scale-105" style="background: {{ $offer['gradient'] }}">
                <x-store.icon name="gift" class="size-12 opacity-90" />
                <span class="text-xl leading-tight font-bold lg:text-2xl">{{ $offer['title'] }}</span>
            </span>
        @endif
    </a>

    <div class="mt-3 flex items-center justify-between gap-2 border-b border-gray-100 pb-3 text-[11px] text-gray-600">
        <span class="flex items-center gap-1.5">
            <x-store.icon name="calendar" class="size-3.5 shrink-0" />
            <time datetime="{{ $offer['ends_at_iso'] }}">{{ $offer['period'] }}</time>
        </span>
        <span class="flex items-center gap-1.5 text-right">
            <x-store.icon name="store" class="size-3.5 shrink-0" />
            {{ $offer['availability'] }}
        </span>
    </div>

    <div class="flex flex-1 flex-col items-center pt-3 text-center">
        <h2 class="text-[15px] font-semibold text-gray-900">
            <a href="{{ $offer['url'] }}" class="transition hover:text-accent">{{ $offer['title'] }}</a>
        </h2>
        @if ($offer['summary'])
            <p class="mt-1.5 line-clamp-2 text-xs leading-5 text-gray-500">{{ $offer['summary'] }}</p>
        @endif
        <div class="mt-auto pt-3.5">
            <a href="{{ $offer['url'] }}" data-ripple class="relative inline-flex items-center overflow-hidden rounded-md bg-builder px-5 py-2 text-xs font-semibold text-white transition hover:brightness-110">View Details</a>
        </div>
    </div>
</article>
