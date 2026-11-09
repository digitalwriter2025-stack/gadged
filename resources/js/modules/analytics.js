/**
 * Shop events raised in the browser. Sent through `window.storeAnalytics`, set up
 * by the Google tag snippet in the page head; nothing happens when no Google
 * tag is configured.
 */
export function track(name, params = {}) {
    window.storeAnalytics?.track(name, params);
}

/**
 * add_to_wishlist for a button carrying its product in `data-analytics-item`.
 */
export function trackWishlist(button) {
    let item;

    try {
        item = JSON.parse(button.dataset.analyticsItem ?? '');
    } catch {
        return;
    }

    const currency = window.storeAnalytics?.currency;

    track('add_to_wishlist', {
        ...(currency ? { currency } : {}),
        ...(item.price ? { value: item.price } : {}),
        items: [{ ...item, quantity: 1 }],
    });
}
