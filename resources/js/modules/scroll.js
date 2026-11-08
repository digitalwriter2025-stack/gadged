let smoothScroller = null;

/**
 * Register the active smooth-scroll engine (Lenis) so shared helpers use it.
 */
export function setSmoothScroller(scroller) {
    smoothScroller = scroller;
}

/**
 * Scroll an element into view, leaving `offset` pixels above it for sticky bars.
 */
export function scrollToElement(element, offset = 0) {
    if (smoothScroller) {
        smoothScroller.scrollTo(element, { offset: -offset });

        return;
    }

    const top = element.getBoundingClientRect().top + window.scrollY - offset;
    window.scrollTo({ top, behavior: 'smooth' });
}

export function scrollToTop() {
    if (smoothScroller) {
        smoothScroller.scrollTo(0);

        return;
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
}
