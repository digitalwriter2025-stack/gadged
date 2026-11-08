/**
 * Global Page Loading Progress Bar
 * Provides instant visual feedback (0ms) when clicking links or submitting forms,
 * making page transitions feel smooth and responsive.
 */
let loaderEl = null;
let barEl = null;
let spinnerEl = null;
let trickleTimer = null;
let currentProgress = 0;
let isRunning = false;

function getElements() {
    if (!loaderEl) {
        loaderEl = document.getElementById('page-loader');
    }
    if (!barEl) {
        barEl = document.getElementById('page-loader-bar');
    }
    if (!spinnerEl) {
        spinnerEl = document.getElementById('page-loader-spinner');
    }
    return { loader: loaderEl, bar: barEl, spinner: spinnerEl };
}

function setProgress(percent) {
    const { bar } = getElements();
    currentProgress = Math.min(100, Math.max(0, percent));
    if (bar) {
        bar.style.width = `${currentProgress}%`;
    }
}

export const PageLoader = {
    start() {
        const { loader, bar, spinner } = getElements();
        if (!loader || !bar) {
            return;
        }

        if (isRunning) {
            return;
        }

        isRunning = true;
        clearInterval(trickleTimer);

        // Immediate visual cue
        bar.style.transition = 'width 0.15s ease-out';
        loader.style.opacity = '1';
        if (spinner) {
            spinner.style.opacity = '1';
        }
        setProgress(25);

        // Simulated intelligent trickle progress
        let step = 0;
        trickleTimer = setInterval(() => {
            step++;
            if (currentProgress < 55) {
                setProgress(currentProgress + 12);
            } else if (currentProgress < 75) {
                setProgress(currentProgress + 6);
            } else if (currentProgress < 88) {
                setProgress(currentProgress + 2);
            } else if (currentProgress < 95) {
                setProgress(currentProgress + 0.5);
            }
        }, 120);
    },

    done() {
        const { loader, bar, spinner } = getElements();
        clearInterval(trickleTimer);

        if (!loader || !bar) {
            isRunning = false;
            return;
        }

        // Snap to 100%
        bar.style.transition = 'width 0.2s ease-out';
        setProgress(100);
        if (spinner) {
            spinner.style.opacity = '0';
        }

        // Fade out
        setTimeout(() => {
            loader.style.opacity = '0';
            setTimeout(() => {
                setProgress(0);
                isRunning = false;
            }, 300);
        }, 200);
    },
};

/**
 * Check if an anchor tag should trigger the page loader.
 */
function isLoadableLink(anchor) {
    if (!anchor || !(anchor instanceof HTMLAnchorElement)) {
        return false;
    }

    const href = anchor.getAttribute('href');
    if (!href || href === '#' || href.startsWith('#') || href.startsWith('javascript:')) {
        return false;
    }

    if (anchor.target && anchor.target !== '_self') {
        return false;
    }

    if (anchor.hasAttribute('download') || anchor.getAttribute('rel') === 'external') {
        return false;
    }

    if (href.startsWith('mailto:') || href.startsWith('tel:')) {
        return false;
    }

    // Must be same origin
    try {
        const url = new URL(anchor.href, window.location.origin);
        if (url.origin !== window.location.origin) {
            return false;
        }

        // Same page with just a hash change
        if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash !== '') {
            return false;
        }

        // Ignore admin links if in storefront (admin handles its own)
        return true;
    } catch {
        return false;
    }
}

export function initPageLoader() {
    // Initial completion if loader was in DOM
    PageLoader.done();

    // Listen for link clicks
    document.addEventListener('click', (event) => {
        // Only primary left clicks without modifier keys
        if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
            return;
        }

        const anchor = event.target instanceof Element ? event.target.closest('a') : null;
        if (anchor && isLoadableLink(anchor)) {
            PageLoader.start();
        }
    });

    // Listen for form submissions (search, filters, etc.)
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (form instanceof HTMLFormElement) {
            if (form.target && form.target !== '_self') {
                return;
            }

            PageLoader.start();

            // Give the submit button a busy state
            const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
            if (submitBtn && submitBtn instanceof HTMLElement) {
                submitBtn.classList.add('is-submitting');
            }
        }
    });

    // Fallback on beforeunload
    window.addEventListener('beforeunload', () => {
        PageLoader.start();
    });

    // Reset when navigating back/forward from bfcache
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            PageLoader.done();
            document.querySelectorAll('.is-submitting').forEach((btn) => btn.classList.remove('is-submitting'));
        }
    });
}
