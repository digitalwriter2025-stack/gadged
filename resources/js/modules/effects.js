import { trackWishlist } from './analytics';
import { prefersReducedMotion } from './motion';

/**
 * Fade lazy images in and drop their skeleton once they load.
 */
export function initImageFade() {
    document.querySelectorAll('img[data-fade]').forEach((image) => {
        const done = () => {
            image.classList.add('is-loaded');
            image.closest('.skeleton')?.classList.remove('skeleton');
        };

        if (image.complete) {
            done();
        } else {
            image.addEventListener('load', done, { once: true });
            image.addEventListener('error', done, { once: true });
        }
    });
}

/**
 * Material-style ripple on `[data-ripple]` elements.
 */
export function initRipples() {
    document.addEventListener('pointerdown', (event) => {
        const target = event.target instanceof Element ? event.target.closest('[data-ripple]') : null;
        if (!target || prefersReducedMotion()) {
            return;
        }

        const rect = target.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height) * 2;
        const ripple = document.createElement('span');

        ripple.className = 'ripple';
        ripple.style.width = `${size}px`;
        ripple.style.height = `${size}px`;
        ripple.style.left = `${event.clientX - rect.left - size / 2}px`;
        ripple.style.top = `${event.clientY - rect.top - size / 2}px`;
        ripple.addEventListener('animationend', () => ripple.remove(), { once: true });

        target.appendChild(ripple);
    });
}

/**
 * Heart buttons on cards: flip `aria-pressed` so the filled state shows.
 */
export function initWishlistToggles() {
    document.addEventListener('click', (event) => {
        const button = event.target instanceof Element ? event.target.closest('[data-wishlist-toggle]') : null;
        if (!button) {
            return;
        }

        const pressed = button.getAttribute('aria-pressed') !== 'true';

        button.setAttribute('aria-pressed', String(pressed));

        if (pressed) {
            trackWishlist(button);
        }
    });
}

/**
 * Keep footer accordions open from `sm` up, collapsed on phones.
 */
export function initFooterAccordions() {
    const accordions = [...document.querySelectorAll('[data-footer-accordion]')];
    if (accordions.length === 0) {
        return;
    }

    const query = window.matchMedia('(min-width: 640px)');
    const sync = () => accordions.forEach((accordion) => {
        accordion.open = query.matches;
    });

    accordions.forEach((accordion) => {
        accordion.querySelector('summary')?.addEventListener('click', (event) => {
            if (query.matches) {
                event.preventDefault();
            }
        });
    });

    query.addEventListener('change', sync);
    sync();
}

/**
 * Forms marked `data-submit-once`: disable the submit button while the request
 * is in flight so a double tap cannot send it twice. Restored on back/forward.
 */
export function initSubmitOnce() {
    const forms = [...document.querySelectorAll('form[data-submit-once]')];
    if (forms.length === 0) {
        return;
    }

    forms.forEach((form) => {
        form.addEventListener('submit', () => {
            form.querySelectorAll('button[type="submit"]').forEach((button) => {
                button.disabled = true;
                button.classList.add('is-loading');
            });
        });
    });

    window.addEventListener('pageshow', (event) => {
        if (!event.persisted) {
            return;
        }

        forms.forEach((form) => form.querySelectorAll('button[type="submit"]').forEach((button) => {
            button.disabled = false;
            button.classList.remove('is-loading');
        }));
    });
}
