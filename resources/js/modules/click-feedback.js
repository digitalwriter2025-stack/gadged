/**
 * Instant Click Feedback & Micro-Interactions
 * Ensures every button tap / click responds in 0ms with tactile visual feedback.
 */
export function initClickFeedback() {
    // Immediate active tap scale effect on interactive elements
    document.addEventListener('pointerdown', (event) => {
        const button = event.target instanceof Element
            ? event.target.closest('button, [role="button"], a.btn, [data-interactive]')
            : null;

        if (!button || button.disabled) {
            return;
        }

        button.classList.add('is-pressed');

        const removePressed = () => {
            button.classList.remove('is-pressed');
            button.removeEventListener('pointerup', removePressed);
            button.removeEventListener('pointercancel', removePressed);
            button.removeEventListener('pointerleave', removePressed);
        };

        button.addEventListener('pointerup', removePressed);
        button.addEventListener('pointercancel', removePressed);
        button.addEventListener('pointerleave', removePressed);
    }, { passive: true });

    // Handle button loading spinners on cart / action triggers
    document.addEventListener('cart:add', (event) => {
        const productId = event.detail?.productId;
        if (!productId) {
            return;
        }

        const buttons = document.querySelectorAll(`[data-listing-cart][data-product-id="${productId}"]`);
        buttons.forEach((btn) => {
            btn.classList.add('is-loading');
            setTimeout(() => {
                btn.classList.remove('is-loading');
                btn.classList.add('is-success');
                setTimeout(() => btn.classList.remove('is-success'), 1200);
            }, 500);
        });
    });
}
