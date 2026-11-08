import { initCarousel } from '../modules/carousel';
import { initHeroSlider } from '../modules/hero-slider';
import { initReveal } from '../modules/reveal';

/**
 * Load animation libraries only for the elements present on this page.
 * Every library is optional: if its chunk fails to load, the vanilla
 * CSS/JS behaviour already in place takes over.
 */
export function loadEnhancements() {
    const heroSliders = [...document.querySelectorAll('[data-hero-slider]')];
    const carousels = [...document.querySelectorAll('[data-carousel]')];

    if (heroSliders.length > 0 || carousels.length > 0) {
        import('./swiper')
            .then(({ enhanceHeroSlider, enhanceCarousel }) => {
                heroSliders.forEach(enhanceHeroSlider);
                carousels.forEach(enhanceCarousel);
            })
            .catch(() => {
                heroSliders.forEach(initHeroSlider);
                carousels.forEach(initCarousel);
            });
    }

    import('./gsap')
        .then(({ enhanceMotion }) => enhanceMotion())
        .catch(() => initReveal());

    const lists = document.querySelectorAll('[data-auto-animate]');
    if (lists.length > 0) {
        import('./extras').then(({ enhanceAutoAnimate }) => enhanceAutoAnimate(lists));
    }

    const lotties = document.querySelectorAll('canvas[data-lottie-src]');
    if (lotties.length > 0) {
        import('./extras').then(({ enhanceLottie }) => enhanceLottie(lotties));
    }

    if (document.querySelector('[data-confetti], [data-confetti-on-load]')) {
        import('./extras').then(({ enhanceConfetti }) => enhanceConfetti());
    }
}
