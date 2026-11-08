import { scrollToTop } from './scroll';

const HIDE_AFTER = 140;
const DELTA = 6;

/**
 * Adds `is-scrolled` once the page moves and, below lg, hides the header while
 * scrolling down and brings it back when scrolling up.
 */
export function initHeader() {
    const header = document.querySelector('[data-site-header]');
    const scrollTop = document.querySelector('[data-scroll-top]');
    let lastY = window.scrollY;
    let ticking = false;

    const update = () => {
        const y = window.scrollY;

        header?.classList.toggle('is-scrolled', y > 8);

        if (Math.abs(y - lastY) > DELTA) {
            header?.classList.toggle('is-hidden', y > lastY && y > HIDE_AFTER);
            lastY = y;
        }

        scrollTop?.classList.toggle('is-visible', y > 600);
        ticking = false;
    };

    window.addEventListener(
        'scroll',
        () => {
            if (!ticking) {
                ticking = true;
                requestAnimationFrame(update);
            }
        },
        { passive: true },
    );

    header?.addEventListener('focusin', () => header.classList.remove('is-hidden'));

    scrollTop?.addEventListener('click', scrollToTop);

    update();
}
