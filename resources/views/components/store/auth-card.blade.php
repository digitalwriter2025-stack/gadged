@props(['title', 'subtitle' => null])

<x-layouts.store :title="$title.' | '.config('app.name')">
    <x-slot:head>
        <meta name="robots" content="noindex, nofollow">
    </x-slot:head>

    <div class="bg-surface py-8 lg:py-14">
        <div class="mx-auto w-full max-w-md px-4">
            <section class="rounded-md border border-gray-200 bg-white p-5 sm:p-7" aria-labelledby="auth-heading">
                <h1 id="auth-heading" class="text-lg font-bold text-gray-900 lg:text-xl">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="mt-1.5 text-[13px] leading-5 text-gray-600">{{ $subtitle }}</p>
                @endif

                @if (session('status'))
                    <p class="mt-4 rounded-md bg-savings/10 px-3 py-2.5 text-xs leading-5 text-savings" role="status">{{ session('status') }}</p>
                @endif

                {{ $slot }}
            </section>

            @isset($footer)
                <p class="mt-4 text-center text-[13px] text-gray-600">{{ $footer }}</p>
            @endisset
        </div>
    </div>
</x-layouts.store>
