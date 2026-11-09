import { trackWishlist } from './analytics';
import { prefersReducedMotion } from './motion';
import { scrollToElement } from './scroll';

const ACTIVE_TAB = ['border-builder', 'text-builder'];
const INACTIVE_TAB = ['border-transparent', 'text-gray-600', 'hover:text-gray-900'];

/**
 * Product page behaviour: image gallery, quantity stepper, sticky section tabs
 * and the add-to-cart / wishlist / compare controls.
 */
export function initProductPage() {
    const page = document.querySelector('[data-product-page]');
    if (!page) {
        return;
    }

    page.querySelectorAll('[data-gallery]').forEach(initGallery);
    page.querySelectorAll('[data-quantity]').forEach(initQuantity);
    initTabs(page);
    initActions(page);
}

function initGallery(gallery) {
    const main = gallery.querySelector('[data-gallery-main]');
    const frame = gallery.querySelector('[data-gallery-zoom]');
    const thumbs = [...gallery.querySelectorAll('[data-gallery-thumb]')];

    thumbs.forEach((thumb) => {
        thumb.addEventListener('click', () => {
            if (thumb.getAttribute('aria-pressed') === 'true') {
                return;
            }

            thumbs.forEach((other) => other.setAttribute('aria-pressed', String(other === thumb)));
            main.style.opacity = '0';

            const swap = () => {
                main.src = thumb.dataset.large;
                main.alt = thumb.dataset.alt;
                main.style.opacity = '';
            };

            const preload = new Image();
            preload.onload = swap;
            preload.onerror = swap;
            preload.src = thumb.dataset.large;
        });
    });

    if (!frame || prefersReducedMotion() || !window.matchMedia('(hover: hover)').matches) {
        return;
    }

    frame.addEventListener('pointermove', (event) => {
        const rect = frame.getBoundingClientRect();
        const x = ((event.clientX - rect.left) / rect.width) * 100;
        const y = ((event.clientY - rect.top) / rect.height) * 100;

        main.style.transformOrigin = `${x}% ${y}%`;
        main.style.scale = '1.8';
        frame.style.cursor = 'zoom-in';
    });

    frame.addEventListener('pointerleave', () => {
        main.style.scale = '';
    });
}

function initQuantity(wrapper) {
    const input = wrapper.querySelector('input');
    const [decrease, increase] = wrapper.querySelectorAll('[data-quantity-step]');
    const min = Number(input.min) || 1;
    const max = Number(input.max) || Infinity;

    const clamp = (value) => Math.min(max, Math.max(min, Number.isFinite(value) ? Math.round(value) : min));

    const render = (value) => {
        input.value = String(clamp(value));
        decrease.disabled = Number(input.value) <= min;
        increase.disabled = Number(input.value) >= max;
    };

    wrapper.querySelectorAll('[data-quantity-step]').forEach((button) => {
        button.addEventListener('click', () => render(Number(input.value) + Number(button.dataset.quantityStep)));
    });

    input.addEventListener('change', () => render(Number(input.value)));
    render(Number(input.value));
}

function initTabs(page) {
    const bar = page.querySelector('[data-product-tabs]');
    const header = document.querySelector('[data-site-header]');
    const tabs = [...page.querySelectorAll('[data-product-tabs] [data-section]')];
    const sections = [...page.querySelectorAll('[data-product-section]')];

    if (!bar || sections.length === 0) {
        return;
    }

    const headerOffset = () => (header && !header.classList.contains('is-hidden') ? header.offsetHeight : 0);

    const setActive = (id) => {
        tabs.forEach((tab) => {
            const isActive = tab.dataset.section === id;
            tab.classList.remove(...(isActive ? INACTIVE_TAB : ACTIVE_TAB));
            tab.classList.add(...(isActive ? ACTIVE_TAB : INACTIVE_TAB));
            tab.toggleAttribute('aria-current', isActive);
        });
    };

    let ticking = false;

    const update = () => {
        const offset = headerOffset();
        bar.style.top = `${offset}px`;

        const line = offset + bar.offsetHeight + 24;
        const current = sections.filter((section) => section.getBoundingClientRect().top <= line).pop() ?? sections[0];
        setActive(current.id);
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
    window.addEventListener('resize', update, { passive: true });

    page.querySelectorAll('[data-product-tab-link]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const target = document.querySelector(link.getAttribute('href'));
            if (!target) {
                return;
            }

            event.preventDefault();
            history.replaceState(null, '', link.getAttribute('href'));
            scrollToElement(target, (header?.offsetHeight ?? 0) + bar.offsetHeight + 12);
        });
    });

    update();
}

function initActions(page) {
    const form = page.querySelector('[data-add-to-cart]');

    form?.addEventListener('submit', (event) => {
        event.preventDefault();

        const quantity = Number(form.querySelector('input[name="quantity"]')?.value ?? 1);

        // The cart module listens for this event; the page itself keeps no cart state.
        document.dispatchEvent(
            new CustomEvent('cart:add', { detail: { productId: Number(form.dataset.productId), quantity } }),
        );
    });

    page.querySelectorAll('[data-wishlist], [data-compare]').forEach((button) => {
        button.addEventListener('click', () => {
            const pressed = button.getAttribute('aria-pressed') === 'true';
            button.setAttribute('aria-pressed', String(!pressed));
            button.classList.toggle('border-accent', !pressed);
            button.classList.toggle('text-accent', !pressed);

            if (!pressed && button.hasAttribute('data-wishlist')) {
                trackWishlist(button);
            }
        });
    });
}
