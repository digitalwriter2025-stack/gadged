export function initCarousel(carousel) {
    const track = carousel.querySelector('[data-carousel-track]');
    const prev = carousel.querySelector('[data-carousel-prev]');
    const next = carousel.querySelector('[data-carousel-next]');
    if (!track) {
        return;
    }

    const step = () => {
        const card = track.firstElementChild;
        const gap = parseFloat(getComputedStyle(track).columnGap) || 0;

        return card ? card.getBoundingClientRect().width + gap : track.clientWidth;
    };

    const syncButtons = () => {
        const maxScroll = track.scrollWidth - track.clientWidth - 2;
        prev?.toggleAttribute('disabled', track.scrollLeft <= 2);
        next?.toggleAttribute('disabled', track.scrollLeft >= maxScroll);
    };

    prev?.addEventListener('click', () => track.scrollBy({ left: -step(), behavior: 'smooth' }));
    next?.addEventListener('click', () => track.scrollBy({ left: step(), behavior: 'smooth' }));
    track.addEventListener('scroll', syncButtons, { passive: true });
    window.addEventListener('resize', syncButtons, { passive: true });

    syncButtons();
}
