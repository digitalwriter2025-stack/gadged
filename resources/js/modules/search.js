import axios from 'axios';

const DEBOUNCE = 250;
const MIN_LENGTH = 2;

const http = axios.create({
    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    timeout: 8000,
});

const escapeHtml = (value) =>
    String(value).replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char]);

const escapeRegExp = (value) => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

/**
 * Escapes the text and wraps every occurrence of the search term in <mark>.
 */
const highlightTerm = (text, term) =>
    escapeHtml(text).replace(
        new RegExp(`(${escapeRegExp(escapeHtml(term))})`, 'gi'),
        '<mark class="bg-transparent font-semibold text-accent">$1</mark>',
    );

const heading = (label) =>
    `<li role="presentation" class="px-3 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-wide text-gray-400">${label}</li>`;

/**
 * Live product and category suggestions under the header search box (loaded with axios),
 * with arrow-key navigation and Escape to close.
 */
export function initSearch() {
    document.querySelectorAll('[data-search]').forEach((form) => {
        const input = form.querySelector('[data-search-input]');
        const results = form.querySelector('[data-search-results]');
        if (!input || !results) {
            return;
        }

        let timer;
        let controller;
        let active = -1;
        const cache = new Map();

        const options = () => [...results.querySelectorAll('[role="option"]')];

        const open = () => {
            results.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        };

        const close = () => {
            results.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            active = -1;
        };

        const highlight = (index) => {
            const items = options();
            if (!items.length) {
                return;
            }
            active = (index + items.length) % items.length;
            items.forEach((item, i) => item.classList.toggle('bg-surface', i === active));
            items[active]?.scrollIntoView({ block: 'nearest' });
        };

        const show = (html) => {
            results.innerHTML = html;
            active = -1;
            open();
        };

        const render = ({ data: products = [], categories = [], meta = {} }, term) => {
            if (!products.length && !categories.length) {
                show(`<li class="px-3 py-3 text-[13px] text-gray-500">No products found for “${escapeHtml(term)}”</li>`);
                return;
            }

            const categoryItems = categories
                .map(
                    (category) => `
                <li role="option">
                    <a href="${escapeHtml(category.url)}" class="flex items-center gap-2 px-3 py-2 text-[13px] transition hover:bg-surface">
                        <svg class="size-4 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 7h18M3 12h18M3 17h18"/></svg>
                        <span class="line-clamp-1">${highlightTerm(category.name, term)}</span>
                    </a>
                </li>`,
                )
                .join('');

            const productItems = products
                .map(
                    (product) => `
                <li role="option">
                    <a href="${escapeHtml(product.url)}" class="flex items-center gap-3 px-3 py-2 transition hover:bg-surface">
                        <img src="${escapeHtml(product.image)}" alt="" width="40" height="40" loading="lazy" class="size-10 shrink-0 rounded object-contain">
                        <span class="min-w-0 flex-1">
                            <span class="line-clamp-1 block text-[13px]">${highlightTerm(product.name, term)}</span>
                            <span class="block text-xs font-semibold text-accent">${escapeHtml(product.price)}</span>
                        </span>
                    </a>
                </li>`,
                )
                .join('');

            const viewAll =
                meta.search_url && products.length
                    ? `
                <li role="option" class="border-t border-gray-100">
                    <a href="${escapeHtml(meta.search_url)}" class="block px-3 py-2.5 text-center text-[13px] font-medium text-accent transition hover:bg-surface">
                        View all ${Number(meta.total) || products.length} results for “${escapeHtml(term)}”
                    </a>
                </li>`
                    : '';

            show(
                (categories.length ? heading('Categories') + categoryItems : '') +
                    (products.length ? heading('Products') + productItems : '') +
                    viewAll,
            );
        };

        const fetchSuggestions = async (term) => {
            const key = term.toLowerCase();
            if (cache.has(key)) {
                render(cache.get(key), term);
                return;
            }

            controller?.abort();
            controller = new AbortController();
            results.setAttribute('aria-busy', 'true');

            try {
                const { data } = await http.get(form.dataset.searchUrl, {
                    params: { q: term },
                    signal: controller.signal,
                });
                cache.set(key, data);

                if (input.value.trim() === term) {
                    render(data, term);
                }
            } catch (error) {
                if (!axios.isCancel(error)) {
                    close();
                }
            } finally {
                results.removeAttribute('aria-busy');
            }
        };

        input.addEventListener('input', () => {
            clearTimeout(timer);
            const term = input.value.trim();

            if (term.length < MIN_LENGTH) {
                controller?.abort();
                close();
                return;
            }

            timer = setTimeout(() => fetchSuggestions(term), DEBOUNCE);
        });

        input.addEventListener('focus', () => {
            if (input.value.trim().length >= MIN_LENGTH && options().length) {
                open();
            }
        });

        input.addEventListener('keydown', (event) => {
            if (results.hidden) {
                return;
            }

            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                highlight(active + (event.key === 'ArrowDown' ? 1 : -1));
            } else if (event.key === 'Enter' && active >= 0) {
                event.preventDefault();
                options()[active]?.querySelector('a')?.click();
            } else if (event.key === 'Escape') {
                close();
            }
        });

        document.addEventListener('click', (event) => {
            if (!form.contains(event.target)) {
                close();
            }
        });
    });
}

/**
 * Keeps aria-expanded in sync on the category mega menu and lets Escape close it.
 * On touch screens the first tap opens the panel and a second tap follows the link.
 */
export function initMegaMenu() {
    document.querySelectorAll('[data-mega-item]').forEach((item) => {
        const trigger = item.querySelector(':scope > a');
        const set = (open) => trigger?.setAttribute('aria-expanded', String(open));
        let followLink = true;

        trigger?.addEventListener('pointerdown', (event) => {
            followLink = event.pointerType !== 'touch' || trigger.getAttribute('aria-expanded') === 'true';
        });
        trigger?.addEventListener('click', (event) => {
            if (!followLink) {
                event.preventDefault();
                trigger.focus();
                set(true);
            }

            followLink = true;
        });

        item.addEventListener('mouseenter', () => set(true));
        item.addEventListener('mouseleave', () => set(false));
        item.addEventListener('focusin', () => set(true));
        item.addEventListener('focusout', (event) => {
            if (!item.contains(event.relatedTarget)) {
                set(false);
            }
        });
        item.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                set(false);
                document.activeElement?.blur();
            }
        });
    });
}
