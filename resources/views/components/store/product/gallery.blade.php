@props(['images', 'savings' => null])

<div {{ $attributes->merge(['class' => 'min-w-0']) }} data-gallery>
    <div class="relative overflow-hidden rounded-md bg-white" data-gallery-zoom>
        @if ($savings)
            <span class="shine absolute top-0 left-0 z-10 rounded-sm bg-savings px-2 py-1 text-[11px] leading-none font-semibold text-white">{{ __('Save: :amount', ['amount' => $savings]) }}</span>
        @endif

        <img src="{{ $images[0]['large'] }}" alt="{{ $images[0]['alt'] }}" width="600" height="600" fetchpriority="high" data-gallery-main class="aspect-square w-full object-contain p-4 transition duration-300 ease-out-expo">
    </div>

    @if (count($images) > 1)
        <ul class="scrollbar-none mt-3 flex gap-2 overflow-x-auto pb-1" aria-label="{{ __('Product images') }}">
            @foreach ($images as $image)
                <li class="shrink-0">
                    <button type="button" data-gallery-thumb data-large="{{ $image['large'] }}" data-alt="{{ $image['alt'] }}" aria-label="{{ __('Show image :number', ['number' => \App\Support\Localization::digits($loop->iteration)]) }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" class="grid size-14 cursor-pointer place-items-center rounded-md border-2 border-gray-200 bg-white p-1 transition hover:border-accent/60 aria-pressed:border-builder sm:size-16">
                        <img src="{{ $image['thumb'] }}" alt="" width="64" height="64" loading="lazy" class="size-full object-contain">
                    </button>
                </li>
            @endforeach
        </ul>
    @endif
</div>
