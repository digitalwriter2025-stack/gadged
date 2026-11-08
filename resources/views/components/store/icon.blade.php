@props(['name'])

@php
    use App\Support\Icons\IconHelper;
    $strokeIcons = IconHelper::STROKE_ICONS;
    $solidIcons = IconHelper::SOLID_ICONS;
@endphp

@if (isset($strokeIcons[$name]))
    <svg {{ $attributes->merge(['class' => 'size-4 shrink-0']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $strokeIcons[$name] !!}</svg>
@elseif (isset($solidIcons[$name]))
    <svg {{ $attributes->merge(['class' => 'size-4 shrink-0']) }} viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">{!! $solidIcons[$name] !!}</svg>
@else
    <svg {{ $attributes->merge(['class' => 'size-4 shrink-0']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/></svg>
@endif
