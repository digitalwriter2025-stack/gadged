<x-layouts.store :title="__('Brands').' | '.config('app.name')" :description="__('Shop gadgets by brand.')">
    <x-slot:head>
        <link rel="canonical" href="{{ route('store.brands.index') }}">
    </x-slot:head>

    <x-store.breadcrumbs :items="$breadcrumbs" container="max-w-hero" />

    <section class="bg-surface">
        <div class="mx-auto max-w-hero px-4 py-6 lg:py-8">
            <h1 class="text-xl font-bold text-gray-900 lg:text-2xl">{{ __('Brands') }}</h1>

            @if ($brands->isEmpty())
                <p class="mt-5 rounded-lg bg-white p-5 text-sm text-gray-600 shadow-sm">{{ __('No brands available right now.') }}</p>
            @else
                <ul class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4 md:grid-cols-6" data-reveal-group>
                    @foreach ($brands as $brand)
                        <li data-reveal>
                            <a href="{{ $brand['url'] }}" class="flex h-full flex-col items-center justify-center gap-2 rounded-md border border-gray-200 bg-white p-3 text-center transition hover:-translate-y-1 hover:border-accent/50 hover:shadow-md">
                                <span class="grid h-14 place-items-center">
                                    @if ($brand['image'])
                                        <img src="{{ $brand['image'] }}" alt="{{ $brand['name'] }}" width="120" height="48" loading="lazy" class="max-h-12 w-auto object-contain">
                                    @else
                                        <span class="text-sm font-semibold text-gray-800">{{ $brand['name'] }}</span>
                                    @endif
                                </span>
                                <span class="text-xs text-gray-500">{{ trans_choice(':count product|:count products', $brand['product_count'], ['count' => \App\Support\Localization::digits($brand['product_count'])]) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>
</x-layouts.store>
