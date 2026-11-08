/**
 * Category listing behaviour: auto-submitting filters, the mobile filter
 * drawer, and the card add-to-cart / compare buttons.
 *
 * Filters are a plain GET form, so everything still works without JS
 * (the search button and the mobile "Apply" button submit it).
 */
export function initCategoryPage() {
    const page = document.querySelector('[data-category-page]');
    if (!page) {
        return;
    }

    const form = page.querySelector('[data-listing-form]');
    if (form) {
        initFilterForm(form);
        initFilterDrawer(page);
    }

    initCardActions(page);
}

function initFilterForm(form) {
    const isDrawerMode = () => !window.matchMedia('(min-width: 1024px)').matches;

    const submit = () => {
        form.setAttribute('aria-busy', 'true');
        form.requestSubmit();
    };

    // Keep the URL short: leave out empty fields and values equal to their defaults.
    form.addEventListener('submit', () => {
        form.querySelectorAll('input[name], select[name]').forEach((field) => {
            const isDefault = field.dataset.default !== undefined && field.value === field.dataset.default;
            if (field.value.trim() === '' || isDefault) {
                field.disabled = true;
            }
        });
    });

    // Restore fields when the page comes back from the bfcache.
    window.addEventListener('pageshow', () => {
        form.querySelectorAll('[disabled]').forEach((field) => {
            field.disabled = false;
        });
        form.removeAttribute('aria-busy');
    });

    // In the mobile drawer shoppers pick several options, then press "Apply".
    form.querySelectorAll('[data-filter-input]').forEach((field) => {
        field.addEventListener('change', () => {
            if (field.tagName === 'SELECT' || !isDrawerMode()) {
                submit();
            }
        });
    });

    form.querySelectorAll('[data-filter-price]').forEach((field) => {
        field.addEventListener('change', () => {
            if (!isDrawerMode()) {
                submit();
            }
        });
    });
}

function initFilterDrawer(page) {
    const panel = page.querySelector('[data-filter-panel]');
    const opener = page.querySelector('[data-filter-open]');
    if (!panel || !opener) {
        return;
    }

    const setOpen = (open) => {
        panel.toggleAttribute('data-open', open);
        opener.setAttribute('aria-expanded', String(open));
        document.documentElement.style.overflow = open ? 'hidden' : '';
    };

    opener.addEventListener('click', () => setOpen(true));
    page.querySelectorAll('[data-filter-close]').forEach((button) => {
        button.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && panel.hasAttribute('data-open')) {
            setOpen(false);
            opener.focus();
        }
    });

    window.matchMedia('(min-width: 1024px)').addEventListener('change', (query) => {
        if (query.matches) {
            setOpen(false);
        }
    });
}

function initCardActions(page) {
    page.querySelectorAll('[data-listing-cart]').forEach((button) => {
        button.addEventListener('click', () => {
            // The cart module listens for this event; the page itself keeps no cart state.
            document.dispatchEvent(
                new CustomEvent('cart:add', { detail: { productId: Number(button.dataset.productId), quantity: 1 } }),
            );
        });
    });

    page.querySelectorAll('[data-compare]').forEach((button) => {
        button.addEventListener('click', () => {
            const pressed = button.getAttribute('aria-pressed') === 'true';
            button.setAttribute('aria-pressed', String(!pressed));
        });
    });
}
