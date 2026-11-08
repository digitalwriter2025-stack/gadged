@props(['title', 'subtitle' => null, 'products', 'initialCount' => 5, 'step' => 5])

<section {{ $attributes->merge(['class' => 'bg-surface']) }} data-product-grid data-step="{{ $step }}" data-step-mobile="4">
    <div class="mx-auto max-w-hero px-4 py-7 lg:px-3 lg:py-8">
        <x-store.section-heading :title="$title" :subtitle="$subtitle" />

        <ul class="mt-5 grid grid-cols-2 items-stretch gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
            @foreach ($products as $product)
                <li data-product-grid-item @if ($loop->index >= $initialCount) hidden @endif>
                    <x-store.product-card :product="$product" />
                </li>
            @endforeach
        </ul>

        @if (count($products) > $initialCount)
            <div class="mt-6 flex flex-col items-center gap-2">
                <button type="button" data-product-grid-more class="group flex h-10 cursor-pointer items-center gap-1.5 rounded-full border border-gray-200 bg-white px-6 text-sm font-medium text-gray-800 transition hover:border-accent hover:bg-accent hover:text-white active:scale-95">
                    View All <x-store.icon name="arrow-right" class="size-3.5 rotate-90" />
                </button>
                <p class="text-[11px] text-gray-500" aria-live="polite">
                    Showing <span data-product-grid-shown>{{ $initialCount }}</span> of {{ count($products) }} products
                </p>
            </div>
        @endif
    </div>
</section>
