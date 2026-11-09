@props(['rating' => 0])

<span {{ $attributes->merge(['class' => 'flex items-center gap-0.5']) }} role="img" aria-label="{{ __('Rated :rating out of 5', ['rating' => \App\Support\Localization::digits($rating)]) }}">
    @for ($star = 1; $star <= 5; $star++)
        <x-store.icon name="star" @class(['size-3.5', 'text-amber-400' => $rating >= $star - 0.5, 'text-gray-300' => $rating < $star - 0.5]) />
    @endfor
</span>
