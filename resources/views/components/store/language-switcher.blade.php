@props(['variant' => 'dark', 'labels' => 'short'])

@php
    $currentLocale = app()->getLocale();
@endphp

{{-- English / বাংলা switch: posts the choice, which is saved in a cookie and reloads the same page. --}}
<form method="POST" action="{{ route('store.locale.update') }}" role="group" aria-label="{{ __('Choose language') }}" data-language-switcher {{ $attributes->class([
    'relative inline-flex shrink-0 items-center rounded-full p-0.5',
    'bg-black/15 ring-1 ring-white/25' => $variant === 'dark',
    'bg-surface ring-1 ring-gray-200' => $variant === 'light',
]) }}>
    @csrf
    @foreach (\App\Support\Localization::locales() as $locale => $language)
        @php($isCurrent = $locale === $currentLocale)
        <button
            type="{{ $isCurrent ? 'button' : 'submit' }}"
            name="locale"
            value="{{ $locale }}"
            lang="{{ $locale }}"
            title="{{ $language['name'] }}"
            aria-pressed="{{ $isCurrent ? 'true' : 'false' }}"
            @class([
                'cursor-pointer rounded-full font-semibold leading-none whitespace-nowrap transition duration-200 active:scale-95',
                'h-7 min-w-9 px-2.5 text-[11px] lg:h-8 lg:min-w-10 lg:px-3 lg:text-xs' => $labels === 'short',
                'h-9 flex-1 px-4 text-sm' => $labels === 'name',
                'bg-white text-accent shadow-sm' => $isCurrent && $variant === 'dark',
                'text-white/85 hover:bg-white/10 hover:text-white' => ! $isCurrent && $variant === 'dark',
                'bg-accent text-white shadow-sm' => $isCurrent && $variant === 'light',
                'text-gray-600 hover:bg-white hover:text-gray-900' => ! $isCurrent && $variant === 'light',
            ])
        >{{ $labels === 'name' ? $language['name'] : $language['short'] }}</button>
    @endforeach
</form>
