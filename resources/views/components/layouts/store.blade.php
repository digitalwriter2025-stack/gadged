@props(['title' => null, 'description' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#ffffff">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? $seo['meta_title'] ?? $site['name'] }}</title>
        @if ($description ?? ($seo['meta_description'] ?? null))
            <meta name="description" content="{{ $description ?? $seo['meta_description'] }}">
        @endif
        @if (isset($seo['robots_index']) && ! $seo['robots_index'])
            <meta name="robots" content="noindex, nofollow">
        @endif
        @if (!empty($seo['og_image']))
            <meta property="og:image" content="{{ $seo['og_image'] }}">
        @endif
        @if (!empty($seo['favicon']))
            <link rel="icon" href="{{ $seo['favicon'] }}">
        @endif

        {{ $head ?? '' }}

        <script>document.documentElement.classList.add('js')</script>

        @include('store.partials.google-tag')

        @if (!empty($metaPixel['domain_verification']))
            <meta name="facebook-domain-verification" content="{{ $metaPixel['domain_verification'] }}">
        @endif

        @if (!empty($metaPixel['pixel_id']))
            <!-- Meta Pixel Code -->
            <script>
                !function(f,b,e,v,n,t,s)
                {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t,s)}(window, document,'script',
                'https://connect.facebook.net/en_US/fbevents.js');
                fbq('init', @json($metaPixel['pixel_id']), @json((object) $metaPixel['advanced_matching']));
                fbq('track', 'PageView', {}, {eventID: @json($metaPixel['page_view_id'])});
                @json($metaPixel['events']).forEach(function (event) {
                    fbq('track', event.name, event.data, {eventID: event.id});
                });
            </script>
            <noscript><img height="1" width="1" style="display:none" alt="" src="https://www.facebook.com/tr?id={{ $metaPixel['pixel_id'] }}&ev=PageView&noscript=1"></noscript>
        @endif

        @if (!empty($analytics['custom_head_scripts']))
            {!! $analytics['custom_head_scripts'] !!}
        @endif

        <link rel="preconnect" href="{{ url('/') }}">
        <link rel="dns-prefetch" href="{{ url('/') }}">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @if ($themeColors !== [])
            <style>:root{@foreach ($themeColors as $variable => $color){{ $variable }}:{{ $color }};@endforeach}</style>
        @endif
    </head>
    <body class="overflow-x-clip bg-white pb-[calc(3.75rem+env(safe-area-inset-bottom))] font-sans text-gray-900 antialiased lg:pb-0" data-server-now="{{ now()->toIso8601String() }}" data-cart-url="{{ route('store.cart.store') }}" data-locale="{{ app()->getLocale() }}">
        <script type="application/json" id="store-i18n">@json(\App\Support\Localization::scriptStrings())</script>
        @if ($googleTag['gtm_id'])
            <!-- Google Tag Manager (noscript) -->
            <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $googleTag['gtm_id'] }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        @endif

        @if (!empty($analytics['custom_body_scripts']))
            {!! $analytics['custom_body_scripts'] !!}
        @endif

        <!-- Top Loading Progress Bar -->
        <div id="page-loader" class="pointer-events-none fixed top-0 inset-x-0 z-[99999] h-[3px] opacity-0 transition-opacity duration-300" role="progressbar" aria-hidden="true">
            <div id="page-loader-bar" class="h-full w-0 bg-gradient-to-r from-accent via-accent/60 to-white shadow-[0_0_12px_color-mix(in_srgb,var(--color-accent)_80%,transparent),0_0_6px_rgba(255,255,255,0.6)] transition-[width] duration-150 ease-out"></div>
            <div id="page-loader-spinner" class="absolute top-2.5 right-3 sm:right-4 size-5 rounded-full border-2 border-accent/20 border-t-accent animate-spin opacity-0 transition-opacity duration-200"></div>
        </div>

        <a href="#main" class="sr-only z-50 rounded bg-accent px-4 py-2 text-white focus:not-sr-only focus:fixed focus:top-2 focus:left-2">{{ __('Skip to content') }}</a>

        @include('store.partials.header')

        <main id="main">
            {{ $slot }}
        </main>

        @include('store.partials.footer')

        <div class="fixed right-2 bottom-[calc(4.5rem+env(safe-area-inset-bottom))] z-40 flex flex-col gap-2 sm:right-3 sm:gap-2.5 lg:bottom-4">
            @if (($floatingSettings['show_hotline'] ?? true) && $contact['hotline_tel'])
                <a href="tel:{{ $contact['hotline_tel'] }}" class="grid size-10 place-items-center sm:size-11 rounded-full border-2 border-white/80 bg-button text-button-text shadow-lg transition duration-300 ease-spring hover:scale-110 hover:bg-button-hover active:scale-95" aria-label="{{ __('Call us') }}">
                    <x-store.icon name="phone" class="size-5" />
                </a>
            @endif
            @if (($floatingSettings['show_whatsapp'] ?? true) && $contact['whatsapp'])
                <a href="https://wa.me/{{ $contact['whatsapp'] }}" target="_blank" rel="noopener" class="relative grid size-10 place-items-center sm:size-11 rounded-full border-2 border-white/80 bg-whatsapp text-white shadow-lg transition duration-300 ease-spring hover:scale-110 hover:bg-[#1da851] active:scale-95" aria-label="{{ __('Chat on WhatsApp') }}">
                    <span class="absolute inset-0 animate-pulse-ring rounded-full bg-whatsapp" aria-hidden="true"></span>
                    <x-store.icon name="whatsapp" class="relative size-6" />
                </a>
            @endif
            @if ($floatingSettings['show_scroll_top'] ?? true)
                <button type="button" data-scroll-top class="grid size-10 cursor-pointer place-items-center sm:size-11 rounded-full border-2 border-white/80 bg-button text-button-text shadow-lg hover:bg-button-hover active:scale-95" aria-label="{{ __('Back to top') }}">
                    <x-store.icon name="arrow-up" class="size-5" />
                </button>
            @endif
        </div>

        @include('store.partials.bottom-nav')
    </body>
</html>
