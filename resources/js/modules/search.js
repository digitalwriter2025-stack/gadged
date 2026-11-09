import axios from 'axios';
import { digits, t } from './i18n';

const DEBOUNCE = 200;
const MIN_LENGTH = 2;
const RECENT_KEY = 'recent-searches';
const RECENT_LIMIT = 6;

const http = axios.create({
    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    timeout: 8000,
});

const escapeHtml = (value) =>
    String(value).replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char]);

const WORD = /[\p{L}\p{M}\p{N}]+/gu;

/**
 * Typos Meilisearch tolerates for a word of this length (its defaults).
 */
const typoBudget = (length) => (length >= 9 ? 2 : length >= 5 ? 1 : 0);

const editDistance = (a, b) => {
    let previous = Array.from({ length: b.length + 1 }, (_, i) => i);
    for (let i = 1; i <= a.length; i++) {
        const current = [i];
        for (let j = 1; j <= b.length; j++) {
            current[j] = Math.min(previous[j] + 1, current[j - 1] + 1, previous[j - 1] + (a[i - 1] === b[j - 1] ? 0 : 1));
        }
        previous = current;
    }

    return previous[b.length];
};

/**
 * Length of the start of `token` matched by the search `word` (prefix match, or
 * within the typo budget so "samsng" marks "Samsung"); 0 when it does not match.
 */
const matchLength = (token, word) => {
    const lower = token.toLowerCase();
    if (lower.startsWith(word)) {
        return word.length;
    }

    const budget = typoBudget(word.length);
    const length = [word.length, word.length - 1, word.length + 1].find(
        (candidate) => budget && candidate > 0 && candidate <= lower.length && editDistance(lower.slice(0, candidate), word) <= budget,
    );

    return length ?? 0;
};

/**
 * Escapes the text and wraps the part of each word matched by the search term in <mark>,
 * so every word of "asus laptop" is marked, typos included.
 */
const highlightTerm = (text, term) => {
    const words = term.toLowerCase().match(WORD) ?? [];
    if (!words.length) {
        return escapeHtml(text);
    }

    return String(text)
        .split(/([\p{L}\p{M}\p{N}]+)/u)
        .map((part, index) => {
            const length = index % 2 ? Math.max(...words.map((word) => matchLength(part, word))) : 0;

            return length
                ? `<mark class="bg-transparent font-semibold text-accent">${escapeHtml(part.slice(0, length))}</mark>${escapeHtml(part.slice(length))}`
                : escapeHtml(part);
        })
        .join('');
};

const heading = (label, action = '') =>
    `<li role="presentation" class="flex items-center justify-between px-4 pb-1.5 pt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400">${label}${action}</li>`;

const icons = {
    clock: '<svg class="size-4 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
    fill: '<svg class="ml-auto size-4 shrink-0 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>',
    arrow: '<svg class="size-4 transition group-hover/all:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
    empty: '<svg class="mx-auto mb-2 size-10 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5M8.5 11h5"/></svg>',
};

/**
 * Recent searches live in localStorage, which can be missing or blocked.
 */
const recentSearches = {
    all() {
        try {
            const stored = JSON.parse(localStorage.getItem(RECENT_KEY) ?? '[]');

            return Array.isArray(stored) ? stored.filter((term) => typeof term === 'string').slice(0, RECENT_LIMIT) : [];
        } catch {
            return [];
        }
    },
    add(term) {
        const clean = term.trim();
        if (clean.length < MIN_LENGTH) {
            return;
        }

        const terms = [clean, ...this.all().filter((saved) => saved.toLowerCase() !== clean.toLowerCase())];
        try {
            localStorage.setItem(RECENT_KEY, JSON.stringify(terms.slice(0, RECENT_LIMIT)));
        } catch {
            // Storage unavailable: recent searches are a convenience only.
        }
    },
    clear() {
        try {
            localStorage.removeItem(RECENT_KEY);
        } catch {
            // Storage unavailable.
        }
    },
};

const skeleton = () =>
    heading(t('Products')) +
    Array.from(
        { length: 3 },
        () => `
        <li role="presentation" class="flex animate-pulse items-center gap-3 px-4 py-2.5">
            <span class="size-12 shrink-0 rounded-md bg-gray-100"></span>
            <span class="flex-1 space-y-2">
                <span class="block h-3 w-4/5 rounded bg-gray-100"></span>
                <span class="block h-3 w-1/3 rounded bg-gray-100"></span>
            </span>
        </li>`,
    ).join('');

const optionClasses = 'outline-none transition data-active:bg-surface hover:bg-surface';

/**
 * Live product and category suggestions under the header search box (loaded with axios):
 * recent searches when empty, a skeleton while loading, arrow-key navigation,
 * Escape to close and "/" to focus the box from anywhere on the page.
 */
export function initSearch() {
    document.querySelectorAll('[data-search]').forEach((form) => {
        const input = form.querySelector('[data-search-input]');
        const results = form.querySelector('[data-search-results]');
        const clearButton = form.querySelector('[data-search-clear]');
        const hint = form.querySelector('[data-search-hint]');
        if (!input || !results) {
            return;
        }

        let timer;
        let controller;
        let active = -1;
        let showingResults = false;
        const cache = new Map();

        const searchUrl = (term) => `${form.action}?q=${encodeURIComponent(term)}`;
        const options = () => [...results.querySelectorAll('[data-search-option]')];

        const open = () => {
            results.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        };

        const close = () => {
            results.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
            active = -1;
        };

        const syncControls = () => {
            const hasValue = input.value.length > 0;
            if (clearButton) {
                clearButton.hidden = !hasValue;
            }
            if (hint) {
                hint.hidden = hasValue || document.activeElement === input;
            }
        };

        const setActive = (index, scroll = true) => {
            const items = options();
            if (!items.length) {
                return;
            }

            active = (index + items.length) % items.length;
            items.forEach((item, i) => item.toggleAttribute('data-active', i === active));
            input.setAttribute('aria-activedescendant', items[active].id);
            if (scroll) {
                items[active].scrollIntoView({ block: 'nearest' });
            }
        };

        const show = (html, isResults = false) => {
            results.innerHTML = html;
            results.classList.remove('opacity-60');
            options().forEach((option, i) => {
                option.id = `${results.id}-option-${i}`;
            });
            active = -1;
            showingResults = isResults;
            input.removeAttribute('aria-activedescendant');
            open();
        };

        const renderRecent = () => {
            const terms = recentSearches.all();
            if (!terms.length) {
                close();
                return;
            }

            const clearAction =
                '<button type="button" data-search-clear-recent class="cursor-pointer text-[11px] font-medium normal-case tracking-normal text-gray-500 transition hover:text-accent">' + t('Clear') + '</button>';

            show(
                heading(t('Recent searches'), clearAction) +
                    terms
                        .map(
                            (term) => `
                    <li role="presentation">
                        <a href="${escapeHtml(searchUrl(term))}" role="option" data-search-option data-term="${escapeHtml(term)}" class="flex items-center gap-3 px-4 py-2 text-[13px] text-gray-700 ${optionClasses}">
                            ${icons.clock}<span class="line-clamp-1">${escapeHtml(term)}</span>${icons.fill}
                        </a>
                    </li>`,
                        )
                        .join('') +
                    '<li role="presentation" class="h-2"></li>',
            );
        };

        const productItem = (product, term) => {
            const meta = [
                product.brand ? `<span class="text-gray-500">${escapeHtml(product.brand)}</span>` : '',
                product.in_stock === false ? `<span class="font-medium text-gray-500">${escapeHtml(product.stock_label)}</span>` : '',
            ]
                .filter(Boolean)
                .join('<span class="text-gray-300">·</span>');

            return `
                <li role="presentation">
                    <a href="${escapeHtml(product.url)}" role="option" data-search-option data-term="${escapeHtml(term)}" class="flex items-center gap-3 px-4 py-2.5 ${optionClasses}">
                        <span class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-md border border-gray-100 bg-white p-1">
                            <img src="${escapeHtml(product.image)}" alt="" width="48" height="48" loading="lazy" class="size-full object-contain">
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="line-clamp-2 block text-[13px] leading-snug text-gray-800">${highlightTerm(product.name, term)}</span>
                            <span class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs">
                                <span class="font-bold text-price">${escapeHtml(product.price)}</span>
                                ${product.regular_price ? `<s class="text-gray-400">${escapeHtml(product.regular_price)}</s>` : ''}
                                ${product.discount_percent ? `<span class="rounded bg-savings/10 px-1.5 py-px text-[10px] font-semibold text-savings">-${digits(Number(product.discount_percent))}%</span>` : ''}
                            </span>
                            ${meta ? `<span class="mt-0.5 flex items-center gap-1.5 text-[11px]">${meta}</span>` : ''}
                        </span>
                    </a>
                </li>`;
        };

        const render = ({ data: products = [], categories = [], meta = {} }, term) => {
            if (!products.length && !categories.length) {
                show(`
                    <li role="presentation" class="px-4 py-8 text-center">
                        ${icons.empty}
                        <p class="text-sm font-medium text-gray-700">${escapeHtml(t('No results for “:term”', { term }))}</p>
                        <p class="mt-1 text-xs text-gray-500">${escapeHtml(t('Check the spelling or try a more general term.'))}</p>
                    </li>`);
                return;
            }

            const categoryChips = categories.length
                ? heading(t('Categories')) +
                  `<li role="presentation" class="flex flex-wrap gap-1.5 px-4 pb-2">${categories
                      .map(
                          (category) => `
                        <a href="${escapeHtml(category.url)}" role="option" data-search-option data-term="${escapeHtml(term)}" class="rounded-full border border-gray-200 px-3 py-1 text-xs text-gray-700 outline-none transition hover:border-accent hover:text-accent data-active:border-accent data-active:text-accent">
                            ${highlightTerm(category.name, term)}
                        </a>`,
                      )
                      .join('')}</li>`
                : '';

            const productItems = products.length ? heading(t('Products')) + products.map((product) => productItem(product, term)).join('') : '';

            const total = Number(meta.total) || products.length;
            const viewAll =
                meta.search_url && products.length
                    ? `
                <li role="presentation" class="sticky bottom-0 mt-1 border-t border-gray-100 bg-white">
                    <a href="${escapeHtml(meta.search_url)}" role="option" data-search-option data-term="${escapeHtml(term)}" class="group/all flex items-center justify-center gap-1.5 px-4 py-3 text-[13px] font-semibold text-accent ${optionClasses}">
                        ${escapeHtml(t(total === 1 ? 'View all :count result' : 'View all :count results', { count: digits(total.toLocaleString('en-US')) }))} ${icons.arrow}
                    </a>
                </li>`
                    : '';

            show(categoryChips + productItems + viewAll, true);
        };

        const renderError = () =>
            show(`
                <li role="presentation" class="px-4 py-6 text-center text-[13px] text-gray-500">
                    Couldn't load suggestions. Press <kbd class="rounded border border-gray-300 px-1 font-sans text-[11px]">Enter</kbd> to search.
                </li>`);

        const fetchSuggestions = async (term) => {
            const key = term.toLowerCase();
            if (cache.has(key)) {
                render(cache.get(key), term);
                return;
            }

            controller?.abort();
            controller = new AbortController();
            results.setAttribute('aria-busy', 'true');

            // Keep the previous results (dimmed) while typing; show a skeleton otherwise.
            if (showingResults && !results.hidden) {
                results.classList.add('opacity-60');
            } else {
                show(skeleton());
            }

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
                if (!axios.isCancel(error) && input.value.trim() === term) {
                    renderError();
                }
            } finally {
                results.removeAttribute('aria-busy');
            }
        };

        const update = () => {
            clearTimeout(timer);
            syncControls();
            const term = input.value.trim();

            if (term.length < MIN_LENGTH) {
                controller?.abort();
                renderRecent();
                return;
            }

            timer = setTimeout(() => fetchSuggestions(term), DEBOUNCE);
        };

        input.addEventListener('input', update);

        const reopen = () => {
            syncControls();
            if (input.value.trim().length < MIN_LENGTH) {
                renderRecent();
            } else if (options().length) {
                open();
            } else {
                update();
            }
        };

        input.addEventListener('focus', reopen);
        input.addEventListener('click', () => results.hidden && reopen());
        input.addEventListener('blur', syncControls);

        input.addEventListener('keydown', (event) => {
            if (results.hidden) {
                return;
            }

            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                setActive(active + (event.key === 'ArrowDown' ? 1 : -1));
            } else if (event.key === 'Enter' && active >= 0) {
                event.preventDefault();
                options()[active]?.click();
            } else if (event.key === 'Escape') {
                close();
            }
        });

        results.addEventListener('mousemove', (event) => {
            const option = event.target.closest('[data-search-option]');
            const index = options().indexOf(option);
            if (index >= 0 && index !== active) {
                setActive(index, false);
            }
        });

        results.addEventListener('click', (event) => {
            if (event.target.closest('[data-search-clear-recent]')) {
                recentSearches.clear();
                close();
                input.focus();
                return;
            }

            const term = event.target.closest('[data-search-option]')?.dataset.term;
            if (term) {
                recentSearches.add(term);
            }
        });

        clearButton?.addEventListener('click', () => {
            input.value = '';
            input.focus();
            update();
        });

        form.addEventListener('submit', () => recentSearches.add(input.value));

        document.addEventListener('click', (event) => {
            if (!event.composedPath().includes(form)) {
                close();
            }
        });

        document.addEventListener('keydown', (event) => {
            const target = event.target;
            const isTyping = target instanceof HTMLElement && (target.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName));

            if (event.key === '/' && !isTyping && !event.ctrlKey && !event.metaKey && !event.altKey && input.offsetParent !== null) {
                event.preventDefault();
                input.focus();
                input.select();
            }
        });

        syncControls();
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
