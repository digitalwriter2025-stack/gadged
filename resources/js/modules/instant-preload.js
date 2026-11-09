/**
 * Instant Link Preloader
 * Prefetches internal pages on hover (65ms intent) or touchstart,
 * so the page is already cached in browser memory when the user finishes clicking.
 */
const prefetchedUrls = new Set();
let isPrefetching = false;
let pendingUrl = null;

function isSupported() {
    if (!document.createElement('link').relList?.supports?.('prefetch')) {
        return false;
    }

    // Check if user has data saver on or is on slow connection
    if (navigator.connection) {
        if (navigator.connection.saveData) {
            return false;
        }
        if (/(2g)/.test(navigator.connection.effectiveType || '')) {
            return false;
        }
    }
    return true;
}

/**
 * One prefetch at a time, and none until the current page has finished
 * loading, so hover prefetches never queue ahead of the page's own requests.
 * While one is in flight only the most recently hovered link waits its turn.
 */
function prefetch(url) {
    if (!isSupported() || prefetchedUrls.has(url)) {
        return;
    }

    if (isPrefetching || document.readyState !== 'complete') {
        pendingUrl = url;

        return;
    }

    prefetchedUrls.add(url);
    isPrefetching = true;

    // Prefer <link rel="prefetch">
    const link = document.createElement('link');
    link.rel = 'prefetch';
    link.href = url;
    link.as = 'document';
    link.onload = prefetchPending;

    // Low priority fetch fallback
    link.onerror = () => {
        fetch(url, { priority: 'low', mode: 'no-cors' })
            .catch(() => {})
            .finally(prefetchPending);
    };

    document.head.appendChild(link);
}

function prefetchPending() {
    isPrefetching = false;

    const url = pendingUrl;
    pendingUrl = null;

    if (url) {
        prefetch(url);
    }
}

function getValidInternalUrl(anchor) {
    if (!anchor || !(anchor instanceof HTMLAnchorElement)) {
        return null;
    }

    const href = anchor.getAttribute('href');
    if (!href || href === '#' || href.startsWith('#') || href.startsWith('javascript:')) {
        return null;
    }

    if (anchor.target && anchor.target !== '_self') {
        return null;
    }

    if (anchor.hasAttribute('download') || anchor.getAttribute('rel') === 'external' || anchor.hasAttribute('data-no-prefetch')) {
        return null;
    }

    if (href.startsWith('mailto:') || href.startsWith('tel:')) {
        return null;
    }

    try {
        const parsed = new URL(anchor.href, window.location.origin);
        if (parsed.origin !== window.location.origin) {
            return null;
        }

        // Don't prefetch current exact URL or admin endpoints
        if (parsed.pathname === window.location.pathname && parsed.search === window.location.search) {
            return null;
        }

        if (parsed.pathname.startsWith('/admin') || parsed.pathname.startsWith('/livewire')) {
            return null;
        }

        return parsed.href;
    } catch {
        return null;
    }
}

export function initInstantPreload() {
    if (!isSupported()) {
        return;
    }

    // Record initial page as prefetched
    prefetchedUrls.add(window.location.href);

    if (document.readyState !== 'complete') {
        window.addEventListener('load', prefetchPending, { once: true });
    }

    let hoverTimeout = null;

    // Prefetch on desktop hover with short 65ms intent threshold
    document.addEventListener(
        'mouseover',
        (event) => {
            const anchor = event.target instanceof Element ? event.target.closest('a') : null;
            const url = getValidInternalUrl(anchor);

            if (!url || prefetchedUrls.has(url)) {
                return;
            }

            clearTimeout(hoverTimeout);
            hoverTimeout = setTimeout(() => {
                prefetch(url);
            }, 65);
        },
        { passive: true },
    );

    document.addEventListener(
        'mouseout',
        (event) => {
            const anchor = event.target instanceof Element ? event.target.closest('a') : null;
            if (anchor) {
                clearTimeout(hoverTimeout);
            }
        },
        { passive: true },
    );

    // Prefetch on mobile touchstart immediately
    document.addEventListener(
        'touchstart',
        (event) => {
            const anchor = event.target instanceof Element ? event.target.closest('a') : null;
            const url = getValidInternalUrl(anchor);
            if (url && !prefetchedUrls.has(url)) {
                prefetch(url);
            }
        },
        { passive: true },
    );
}
