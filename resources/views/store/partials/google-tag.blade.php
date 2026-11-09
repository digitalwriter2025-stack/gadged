{{--
    Google tags for the storefront <head>: GA4 / Google Ads through gtag.js, Tag Manager,
    and the shop events from App\Services\GoogleTag. Each event is pushed once:
    to the dataLayer in GA4 ecommerce format for Tag Manager, and through gtag()
    with an explicit send_to so a Google tag inside Tag Manager does not get it too.
--}}
@if ($googleTag['enabled'])
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
    </script>

    @if ($googleTag['measurement_id'] || $googleTag['ads_id'])
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $googleTag['measurement_id'] ?? $googleTag['ads_id'] }}"></script>
        <script>
            gtag('js', new Date());
            @if ($googleTag['measurement_id'])
                gtag('config', @json($googleTag['measurement_id']));
            @endif
            @if ($googleTag['ads_id'])
                gtag('config', @json($googleTag['ads_id']));
            @endif
        </script>
    @endif

    @if ($googleTag['gtm_id'])
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer',@json($googleTag['gtm_id']));</script>
        <!-- End Google Tag Manager -->
    @endif

    <script>
        (function (config, events) {
            var storageKey = 'google_tag.sent';

            // A page shown again from the history cache re-runs this script; skip events already sent.
            function alreadySent(id) {
                try {
                    var sent = JSON.parse(sessionStorage.getItem(storageKey) || '[]');

                    if (sent.indexOf(id) !== -1) {
                        return true;
                    }

                    sent.push(id);
                    sessionStorage.setItem(storageKey, JSON.stringify(sent.slice(-100)));
                } catch (error) {
                    // Storage blocked: send anyway, GA4 and Ads still de-duplicate purchases by transaction_id.
                }

                return false;
            }

            function track(name, params, id, userData) {
                params = params || {};

                if (id && alreadySent(id)) {
                    return;
                }

                if (config.gtm) {
                    var message = { event: name, event_id: id || null };

                    if (params.items) {
                        // Clear the previous ecommerce object so its fields do not leak into this event.
                        dataLayer.push({ ecommerce: null });
                        message.ecommerce = params;
                    } else {
                        Object.keys(params).forEach(function (key) {
                            message[key] = params[key];
                        });
                    }

                    if (userData) {
                        message.user_data = userData;
                    }

                    dataLayer.push(message);
                }

                if (userData && (config.ga4 || config.adsConversion)) {
                    gtag('set', 'user_data', userData);
                }

                if (config.ga4) {
                    gtag('event', name, Object.assign({ send_to: config.ga4 }, params));
                }

                if (name === 'purchase' && config.adsConversion) {
                    gtag('event', 'conversion', {
                        send_to: config.adsConversion,
                        value: params.value,
                        currency: params.currency,
                        transaction_id: params.transaction_id
                    });
                }
            }

            window.storeAnalytics = { track: track, currency: config.currency };

            events.forEach(function (event) {
                track(event.name, event.params, event.id, event.user_data);
            });
        })(@json($googleTag['browser']), @json($googleTag['events']));
    </script>
@endif
