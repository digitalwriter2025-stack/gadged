<x-layouts.store :title="$heading.' | '.config('app.name')" :description="$intro">
    <x-slot:head>
        <link rel="canonical" href="{{ url()->current() }}">
    </x-slot:head>

    <x-store.breadcrumbs :items="$breadcrumbs" container="max-w-hero" />

    <section class="bg-surface" data-offer-page>
        <div class="mx-auto max-w-hero px-4 py-6 lg:py-8">
            <header class="mb-5 xl:mx-auto xl:max-w-[1100px]">
                <h1 class="text-lg font-bold text-gray-900 lg:text-2xl">{{ $heading }}</h1>
                <p class="mt-1.5 max-w-2xl text-[13px] leading-6 text-gray-600">{{ $intro }}</p>
            </header>

            @if ($offers === [])
                <div class="rounded-lg bg-white px-6 py-16 text-center shadow-sm">
                    <x-store.icon name="gift" class="mx-auto size-12 text-gray-300" />
                    <p class="mt-4 text-base font-semibold text-gray-800">{{ __('No campaigns running right now') }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ __('New campaigns are announced here first. Meanwhile, combo deals bundle popular gadgets for less.') }}</p>
                    <a href="{{ route('store.combos.index') }}" class="mt-5 inline-block rounded-md bg-button px-5 py-2 text-sm font-semibold text-button-text transition hover:bg-button-hover">{{ __('See Combo Deals') }}</a>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 min-[480px]:grid-cols-2 lg:grid-cols-3 lg:gap-5 xl:mx-auto xl:max-w-[1100px] xl:gap-7">
                    @foreach ($offers as $offer)
                        <x-store.offer-card :offer="$offer" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-layouts.store>
