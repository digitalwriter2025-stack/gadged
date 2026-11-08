const MAX_STAGGER = 8;

/**
 * Fade elements in as they scroll into view. A `[data-reveal-group]` reveals
 * all of its `[data-reveal]` children together, staggered in DOM order.
 */
export function initReveal() {
    const groups = [...document.querySelectorAll('[data-reveal-group]')];
    const grouped = new Set();

    groups.forEach((group) => {
        group.querySelectorAll('[data-reveal]').forEach((item, index) => {
            item.style.setProperty('--reveal-index', String(Math.min(index, MAX_STAGGER)));
            grouped.add(item);
        });
    });

    const singles = [...document.querySelectorAll('[data-reveal]')].filter((item) => !grouped.has(item));

    if (!('IntersectionObserver' in window)) {
        document.querySelectorAll('[data-reveal]').forEach((item) => item.classList.add('is-visible'));

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                const target = entry.target;
                const items = target.hasAttribute('data-reveal-group')
                    ? target.querySelectorAll('[data-reveal]')
                    : [target];

                items.forEach((item) => item.classList.add('is-visible'));
                observer.unobserve(target);
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.1 },
    );

    [...groups, ...singles].forEach((target) => observer.observe(target));
}
