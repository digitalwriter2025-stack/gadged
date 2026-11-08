@props(['width' => 264, 'height' => 22])

@php($site = app(\App\Support\Storefront\StoreLayout::class)->site())

@if ($site['logo'])
    <img src="{{ $site['logo'] }}" alt="{{ $site['name'] }}" width="{{ $width }}" height="{{ $height }}" {{ $attributes->merge(['class' => 'w-auto object-contain']) }}>
@else
    <span {{ $attributes->class(['block font-extrabold tracking-tight whitespace-nowrap text-current text-lg leading-none lg:text-2xl']) }}>{{ $site['name'] }}</span>
@endif
