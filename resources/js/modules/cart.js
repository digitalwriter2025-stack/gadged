import { digits, t } from './i18n';

/**
 * Cart: handles the `cart:add` event fired by the product page and the listing
 * cards by posting the product to the cart, which redirects to the checkout
 * (order) page. A plain form post keeps the session cart server-side only.
 */
export function initCart() {
    const action = document.body.dataset.cartUrl;
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!action || !token) {
        return;
    }

    let submitting = false;

    document.addEventListener('cart:add', (event) => {
        const productId = Number(event.detail?.productId);
        const quantity = Math.max(1, Number(event.detail?.quantity) || 1);

        if (!productId || submitting) {
            return;
        }

        submitting = true;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action;
        form.hidden = true;

        Object.entries({ _token: token, product_id: productId, quantity }).forEach(([name, value]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = String(value);
            form.append(input);
        });

        document.body.append(form);
        form.submit();
    });

    // Allow adding again after the page is restored from the back/forward cache.
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            submitting = false;
        }
    });
}

/**
 * Checkout page: live delivery charge / total for the chosen district (the
 * server recalculates both when the order is placed) and a double-submit guard.
 */
export function initCheckout() {
    const page = document.querySelector('[data-checkout]');
    if (!page) {
        return;
    }

    const symbol = page.dataset.currencySymbol ?? '';
    const subtotal = Number(page.dataset.subtotal ?? 0);
    const methods = page.querySelectorAll('[data-shipping-method]');
    const shippingOutput = page.querySelector('[data-shipping-total]');
    const totalOutput = page.querySelector('[data-grand-total]');
    const formatter = new Intl.NumberFormat('en-US', { maximumFractionDigits: 2 });
    const format = (amount) => `${symbol}${digits(formatter.format(amount))}`;

    const setText = (element, text) => {
        if (element) {
            element.textContent = text;
        }
    };

    const update = () => {
        if (methods.length === 0) {
            return;
        }

        const selected = [...methods].find((input) => input.checked);

        if (!selected) {
            setText(shippingOutput, t('Select delivery method'));
            setText(totalOutput, format(subtotal));

            return;
        }

        const shipping = Number(selected.dataset.shipping || 0);

        setText(shippingOutput, shipping > 0 ? format(shipping) : t('Free'));
        setText(totalOutput, format(subtotal + shipping));
    };

    methods.forEach((input) => input.addEventListener('change', update));
    update();

    const form = page.querySelector('[data-checkout-form]');
    const button = page.querySelector('[data-place-order]');

    if (!form || !button) {
        return;
    }

    const initiallyDisabled = button.disabled;

    form.addEventListener('submit', () => {
        button.disabled = true;
        button.classList.add('is-loading');
    });

    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            button.disabled = initiallyDisabled;
            button.classList.remove('is-loading');
        }
    });
}

/**
 * Order invoice on the thank-you page: print it (or "Save as PDF").
 */
export function initInvoicePrint() {
    document.querySelector('[data-print-invoice]')?.addEventListener('click', () => window.print());
}
