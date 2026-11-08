import { prefersReducedMotion } from './motion';

/**
 * Reveal the hidden cards of a `[data-product-grid]` a few at a time.
 * The button disappears once every card is visible.
 */
export function initProductGrids() {
    const isMobile = () => window.matchMedia('(max-width: 639px)').matches;

    document.querySelectorAll('[data-product-grid]').forEach((grid) => {
        const button = grid.querySelector('[data-product-grid-more]');
        const counter = grid.querySelector('[data-product-grid-shown]');
        const items = [...grid.querySelectorAll('[data-product-grid-item]')];

        if (items.length === 0) {
            return;
        }

        let mobileAdjusted = false;

        const syncInitialMobile = () => {
            if (isMobile() && !mobileAdjusted) {
                const visible = items.filter((item) => !item.hidden);
                // On mobile 2-column grid, an odd number leaves an empty gap next to the last item.
                // Hide the trailing odd card so rows are fully filled (e.g. 4 cards instead of 5).
                if (visible.length > 2 && visible.length % 2 !== 0) {
                    const trailingItem = visible[visible.length - 1];
                    trailingItem.hidden = true;
                    trailingItem.dataset.autoHiddenMobile = 'true';
                    mobileAdjusted = true;
                    if (counter) {
                        counter.textContent = String(items.filter((item) => !item.hidden).length);
                    }
                }
            } else if (!isMobile() && mobileAdjusted) {
                const autoHidden = grid.querySelector('[data-auto-hidden-mobile]');
                if (autoHidden) {
                    autoHidden.hidden = false;
                    delete autoHidden.dataset.autoHiddenMobile;
                    mobileAdjusted = false;
                    if (counter) {
                        counter.textContent = String(items.filter((item) => !item.hidden).length);
                    }
                }
            }
        };

        syncInitialMobile();
        window.addEventListener('resize', syncInitialMobile);

        if (!button) {
            return;
        }

        button.addEventListener('click', () => {
            const autoHidden = grid.querySelector('[data-auto-hidden-mobile]');
            if (autoHidden) {
                delete autoHidden.dataset.autoHiddenMobile;
            }
            mobileAdjusted = true;

            const step = isMobile()
                ? (Number(grid.dataset.stepMobile) || 4)
                : (Number(grid.dataset.step) || 5);

            const hiddenItems = items.filter((item) => item.hidden);
            const next = hiddenItems.slice(0, step);

            next.forEach((item, index) => {
                item.hidden = false;

                if (!prefersReducedMotion() && typeof item.animate === 'function') {
                    item.animate(
                        [
                            { opacity: 0, transform: 'translateY(16px)' },
                            { opacity: 1, transform: 'none' },
                        ],
                        { duration: 400, delay: index * 70, easing: 'cubic-bezier(0.16, 1, 0.3, 1)', fill: 'backwards' },
                    );
                }
            });

            const remaining = items.filter((item) => item.hidden).length;

            if (counter) {
                counter.textContent = String(items.length - remaining);
            }

            if (remaining === 0) {
                button.hidden = true;
            }
        });
    });
}
