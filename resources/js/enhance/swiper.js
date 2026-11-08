import Swiper from 'swiper';
import { A11y, Autoplay, EffectFade, Keyboard, Scrollbar } from 'swiper/modules';
import { prefersReducedMotion } from '../modules/motion';

const HERO_DELAY = 5000;

/**
 * Hero banner: cross-fade, drag/swipe, keyboard, autoplay with the dot
 * underneath each slide filling up as its time runs out.
 */
export function enhanceHeroSlider(slider) {
    const viewport = slider.querySelector('[data-hero-viewport]');
    const dots = [...slider.querySelectorAll('[data-slide-dot]')];
    const reduced = prefersReducedMotion();
    const slideCount = slider.querySelectorAll('[data-slide]').length;

    const syncDots = (swiper) => {
        dots.forEach((dot, i) => {
            const isActive = i === swiper.realIndex;
            dot.classList.toggle('is-active', isActive);
            dot.setAttribute('aria-current', String(isActive));
            dot.querySelector('.hero-dot-fill')?.style.removeProperty('scale');
        });
    };

    slider.classList.remove('is-playing');

    const swiper = new Swiper(viewport, {
        modules: [A11y, Autoplay, EffectFade, Keyboard],
        wrapperClass: 'swiper-wrapper',
        slideClass: 'swiper-slide',
        effect: 'fade',
        fadeEffect: { crossFade: true },
        // Swiper's autoplay stalls after one slide when speed is 0 with fade + loop,
        // so reduced motion keeps a short cross-fade instead.
        speed: reduced ? 300 : 700,
        loop: slideCount > 1,
        grabCursor: true,
        keyboard: { enabled: true, onlyInViewport: true },
        a11y: { enabled: true, containerRoleDescriptionMessage: null, itemRoleDescriptionMessage: 'slide' },
        autoplay: slideCount < 2
            ? false
            : { delay: HERO_DELAY, disableOnInteraction: false },
        on: {
            init: syncDots,
            slideChange: syncDots,
            autoplayTimeLeft(instance, timeLeft, percentage) {
                const fill = dots[instance.realIndex]?.querySelector('.hero-dot-fill');
                if (fill) {
                    fill.style.scale = `${1 - percentage} 1`;
                }
            },
        },
    });

    dots.forEach((dot, i) => dot.addEventListener('click', () => swiper.slideToLoop(i)));
    slider.querySelector('[data-slide-prev]')?.addEventListener('click', () => swiper.slidePrev());
    slider.querySelector('[data-slide-next]')?.addEventListener('click', () => swiper.slideNext());

    slider.addEventListener('focusin', (event) => {
        if (event.target.matches(':focus-visible')) {
            swiper.autoplay?.pause();
        }
    });
    slider.addEventListener('focusout', () => swiper.autoplay?.resume());

    return swiper;
}

const PRODUCT_ROW_LAYOUT = {
    slidesPerView: 1.7,
    spaceBetween: 12,
    breakpoints: {
        360: { slidesPerView: 2.25 },
        640: { slidesPerView: 3 },
        768: { slidesPerView: 4 },
        1024: { slidesPerView: 5 },
    },
};

/**
 * Promo panel + carousel rows: a partly visible last card hints there is
 * more to scroll. The promo panel takes part of the row, so breakpoints
 * follow the carousel's own width (matches the @container fallback widths
 * in showcase-carousel).
 */
const SHOWCASE_LAYOUT = {
    breakpointsBase: 'container',
    slidesPerView: 1.6,
    spaceBetween: 12,
    breakpoints: {
        300: { slidesPerView: 2.1 },
        448: { slidesPerView: 3.2, slidesPerGroup: 2 },
        576: { slidesPerView: 3.6, slidesPerGroup: 3 },
        768: { slidesPerView: 4.3, slidesPerGroup: 4 },
    },
};

/**
 * Product rows: drag with mouse or touch, prev/next buttons that disable at
 * the ends, cards-per-view by breakpoint (matches the CSS fallback widths).
 */
export function enhanceCarousel(carousel) {
    const viewport = carousel.querySelector('[data-carousel-viewport]');
    const prev = carousel.querySelector('[data-carousel-prev]');
    const next = carousel.querySelector('[data-carousel-next]');
    const scrollbar = carousel.querySelector('[data-carousel-scrollbar]');
    if (!viewport) {
        return null;
    }

    // Revealed before init so Swiper can measure the track.
    if (scrollbar) {
        scrollbar.hidden = false;
    }

    const syncButtons = (swiper) => {
        prev?.toggleAttribute('disabled', swiper.isBeginning);
        next?.toggleAttribute('disabled', swiper.isEnd);
    };

    const swiper = new Swiper(viewport, {
        modules: [Scrollbar],
        wrapperClass: 'swiper-wrapper',
        slideClass: 'swiper-slide',
        speed: prefersReducedMotion() ? 0 : 500,
        grabCursor: true,
        watchOverflow: true,
        threshold: 6,
        ...(carousel.dataset.carousel === 'showcase' ? SHOWCASE_LAYOUT : PRODUCT_ROW_LAYOUT),
        ...(scrollbar && { scrollbar: { el: scrollbar, draggable: true } }),
        on: {
            init: syncButtons,
            slideChange: syncButtons,
            reachBeginning: syncButtons,
            reachEnd: syncButtons,
            fromEdge: syncButtons,
            resize: syncButtons,
        },
    });

    prev?.addEventListener('click', () => swiper.slidePrev());
    next?.addEventListener('click', () => swiper.slideNext());

    return swiper;
}
