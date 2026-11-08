<x-layouts.store :title="$offer['title'].' | '.config('app.name')" :description="$offer['summary']">
    <x-slot:head>
        <link rel="canonical" href="{{ $offer['url'] }}">
    </x-slot:head>

    <x-store.breadcrumbs :items="$breadcrumbs" container="max-w-hero" />

    <section class="bg-surface">
        <div class="mx-auto max-w-hero px-4 py-6 lg:py-8">
            <div class="grid gap-6 rounded-lg bg-white p-4 shadow-sm md:grid-cols-[minmax(0,22rem)_minmax(0,1fr)] lg:gap-10 lg:p-6">
                <div class="aspect-square overflow-hidden rounded-md bg-white">
                    @if ($offer['image_large'])
                        <img src="{{ $offer['image_large'] }}" alt="{{ $offer['title'] }}" width="900" height="900" class="size-full {{ $offer['image_fit'] }}">
                    @else
                        <div class="flex size-full flex-col items-center justify-center gap-3 p-6 text-center text-white" style="background: {{ $offer['gradient'] }}">
                            <x-store.icon name="gift" class="size-14 opacity-90" />
                            <span class="text-2xl leading-tight font-bold">{{ $offer['title'] }}</span>
                        </div>
                    @endif
                </div>

                <div class="min-w-0">
                    <h1 class="text-xl font-bold text-gray-900 lg:text-2xl">{{ $offer['title'] }}</h1>
                    @if ($offer['summary'])
                        <p class="mt-2 text-sm text-gray-600">{{ $offer['summary'] }}</p>
                    @endif

                    <dl class="mt-4 flex flex-wrap gap-2 text-xs">
                        <div class="flex items-center gap-1.5 rounded-full bg-accent/10 px-3 py-1.5 text-accent">
                            <x-store.icon name="calendar" class="size-3.5" />
                            <dt class="sr-only">Offer period</dt>
                            <dd><time datetime="{{ $offer['ends_at_iso'] }}">{{ $offer['period'] }}</time></dd>
                        </div>
                        <div class="flex items-center gap-1.5 rounded-full bg-builder/10 px-3 py-1.5 text-builder">
                            <x-store.icon name="store" class="size-3.5" />
                            <dt class="sr-only">Available at</dt>
                            <dd>{{ $offer['availability'] }}</dd>
                        </div>
                    </dl>

                    @if ($description)
                        <div class="mt-5 border-t border-gray-100 pt-5 text-sm leading-7 whitespace-pre-line text-gray-700">{{ $description }}</div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @if ($products !== [])
        <x-store.product-grid title="Offer Products" :subtitle="count($products).' products in this offer'" :products="$products" :initial-count="10" :step="10" />
    @endif
</x-layouts.store>
