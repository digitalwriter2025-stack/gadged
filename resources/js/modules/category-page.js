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
        const submitFilters = initFilterForm(form);
        initPriceRange(form, submitFilters);
        initOptionSearch(form);
        initFilterDrawer(page);
    }

    initCardActions(page);
}

/**
 * Wires auto-submit and URL tidying. Returns the function that applies the
 * filters right away on desktop (the drawer waits for its "Apply" button).
 */
function initFilterForm(form) {
    const isDrawerMode = () => !window.matchMedia('(min-width: 1024px)').matches;

    const submitUnlessDrawer = () => {
        if (!isDrawerMode()) {
            form.requestSubmit();
        }
    };

    form.addEventListener('submit', () => {
        form.setAttribute('aria-busy', 'true');
        swapReversedPrices(form);

        // Keep the URL short: leave out empty fields and values equal to their defaults.
        form.querySelectorAll('input[name], select[name]').forEach((field) => {
            const isDefault = field.dataset.default !== undefined && field.value === field.dataset.default;
            if (!field.disabled && (field.value.trim() === '' || isDefault)) {
                field.disabled = true;
                field.dataset.skipped = '';
            }
        });
    });

    // Restore the fields we skipped when the page comes back from the bfcache.
    window.addEventListener('pageshow', () => {
        form.querySelectorAll('[data-skipped]').forEach((field) => {
            field.disabled = false;
            delete field.dataset.skipped;
        });
        form.removeAttribute('aria-busy');
    });

    // In the mobile drawer shoppers pick several options, then press "Apply".
    form.querySelectorAll('[data-filter-input]').forEach((field) => {
        field.addEventListener('change', () => {
            if (field.tagName === 'SELECT') {
                form.requestSubmit();

                return;
            }

            submitUnlessDrawer();
        });
    });

    return submitUnlessDrawer;
}

/**
 * A shopper who types the bigger number first still gets the range they meant.
 */
function swapReversedPrices(form) {
    const min = form.querySelector('input[name="min"]');
    const max = form.querySelector('input[name="max"]');
    if (!min || !max || min.value === '' || max.value === '' || Number(min.value) <= Number(max.value)) {
        return;
    }

    [min.value, max.value] = [max.value, min.value];
}

/**
 * Keeps the two-thumb slider and the min / max boxes in step. The boxes are
 * the submitted fields; the sliders have no name and only drive them.
 */
function initPriceRange(form, submitFilters) {
    const range = form.querySelector('[data-price-range]');
    if (!range) {
        return;
    }

    // Tabbing from the min box to the max box must not reload the page halfway
    // through a range: apply once focus leaves the price block (Enter submits natively).
    let hasTypedPrice = false;
    range.querySelectorAll('[data-filter-price]').forEach((field) => {
        field.addEventListener('change', () => {
            hasTypedPrice = true;
        });
    });

    range.addEventListener('focusout', (event) => {
        if (hasTypedPrice && !range.contains(event.relatedTarget)) {
            hasTypedPrice = false;
            submitFilters();
        }
    });

    const minSlider = range.querySelector('[data-price-slider="min"]');
    const maxSlider = range.querySelector('[data-price-slider="max"]');
    if (!minSlider || !maxSlider) {
        return;
    }

    const minField = range.querySelector('input[name="min"]');
    const maxField = range.querySelector('input[name="max"]');
    const track = range.querySelector('[data-price-track]');
    const lowest = Number(minSlider.min);
    const highest = Number(minSlider.max);
    const step = Number(minSlider.step) || 1;
    const span = highest - lowest;

    // The last step can stop short of the real maximum (102,490 of 102,500): treat it as the maximum.
    const priceOf = (slider) => {
        const value = Number(slider.value);

        return value + step > highest ? highest : value;
    };

    const percentOf = (value) => ((Math.min(Math.max(value, lowest), highest) - lowest) / span) * 100;

    const paint = () => {
        const from = Number(minSlider.value);
        track.style.setProperty('--from', `${percentOf(from)}%`);
        track.style.setProperty('--to', `${percentOf(Number(maxSlider.value))}%`);
        // When both thumbs meet at the right end, the min thumb must stay grabbable.
        minSlider.style.zIndex = from > lowest + span / 2 ? '2' : '';
    };

    const slide = (event) => {
        if (Number(minSlider.value) > Number(maxSlider.value)) {
            if (event.target === minSlider) {
                minSlider.value = maxSlider.value;
            } else {
                maxSlider.value = minSlider.value;
            }
        }

        minField.value = String(priceOf(minSlider));
        maxField.value = String(priceOf(maxSlider));
        paint();
    };

    // Arrow keys fire "change" on every press, so wait for the shopper to settle before reloading.
    let commitTimer;
    const commit = () => {
        clearTimeout(commitTimer);
        commitTimer = setTimeout(submitFilters, 400);
    };

    [minSlider, maxSlider].forEach((slider) => {
        slider.addEventListener('input', slide);
        slider.addEventListener('change', commit);
    });

    // Typing in the boxes moves the thumbs.
    const follow = () => {
        minSlider.value = minField.value === '' ? String(lowest) : minField.value;
        maxSlider.value = maxField.value === '' ? String(highest) : maxField.value;
        paint();
    };

    minField.addEventListener('input', follow);
    maxField.addEventListener('input', follow);
}

/**
 * Long option lists (brands, specifications) get a box that narrows them as you type.
 */
function initOptionSearch(form) {
    form.querySelectorAll('[data-filter-search]').forEach((input) => {
        const group = input.closest('[data-filter-group]');
        const options = [...group.querySelectorAll('[data-filter-option]')];
        const empty = group.querySelector('[data-filter-search-empty]');

        input.addEventListener('input', () => {
            const term = input.value.trim().toLowerCase();
            let visible = 0;

            options.forEach((option) => {
                const matches = option.dataset.filterOption.includes(term);
                option.hidden = !matches;
                visible += matches ? 1 : 0;
            });

            if (empty) {
                empty.hidden = visible > 0;
            }
        });

        // Enter here narrows the list; it must not submit the whole form.
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
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

        if (open) {
            panel.querySelector('button[data-filter-close]')?.focus({ preventScroll: true });
        }
    };

    opener.addEventListener('click', () => setOpen(true));
    page.querySelectorAll('[data-filter-close]').forEach((button) => {
        button.addEventListener('click', () => {
            setOpen(false);
            opener.focus({ preventScroll: true });
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && panel.hasAttribute('data-open')) {
            setOpen(false);
            opener.focus({ preventScroll: true });
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
