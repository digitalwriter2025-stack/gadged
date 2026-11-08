<x-layouts.store :title="($page->seo_title ?: $page->title).' | '.config('app.name')" :description="$page->seo_description">
    <x-slot:head>
        <link rel="canonical" href="{{ route('store.pages.show', $page) }}">
    </x-slot:head>

    <x-store.breadcrumbs :items="$breadcrumbs" container="max-w-hero" />

    <section class="bg-surface">
        <div class="mx-auto max-w-hero px-4 py-6 lg:py-8">
            <article class="rounded-lg bg-white p-5 shadow-sm lg:p-8">
                <h1 class="text-xl font-bold text-gray-900 lg:text-2xl">{{ $page->title }}</h1>

                @if (filled($page->content))
                    <div class="cms-content mt-5 border-t border-gray-100 pt-5 text-sm leading-7 text-gray-700">
                        {!! \Filament\Forms\Components\RichEditor\RichContentRenderer::make($page->content)->toHtml() !!}
                    </div>
                @endif
            </article>
        </div>
    </section>
</x-layouts.store>
