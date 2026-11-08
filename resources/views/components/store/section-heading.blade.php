@props(['title', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'min-w-0 flex-1']) }} data-reveal>
    <div class="flex items-center gap-2">
        <span class="heading-bar block h-5 w-1 shrink-0 rounded-full bg-accent sm:hidden" aria-hidden="true"></span>
        <h2 data-split class="truncate text-lg leading-tight font-bold tracking-tight text-gray-900 sm:text-section-title">{{ $title }}</h2>
    </div>
    <span class="heading-bar mt-1.5 hidden h-[3px] w-10 rounded-full bg-accent sm:block" aria-hidden="true"></span>
    @if ($subtitle)
        <p class="mt-1 truncate pl-3 text-[11px] text-gray-500 sm:mt-1.5 sm:pl-0 sm:text-xs sm:text-gray-600 sm:whitespace-normal lg:text-[11px]">{{ $subtitle }}</p>
    @endif
</div>
