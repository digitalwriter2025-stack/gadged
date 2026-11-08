import { replayClass } from './motion';

const INTERVAL = 5000;
const SWIPE_THRESHOLD = 40;

export function initHeroSlider(slider) {
    const slides = [...slider.querySelectorAll('[data-slide]')];
    const dots = [...slider.querySelectorAll('[data-slide-dot]')];
    const autoplay = slides.length > 1;
    let current = 0;
    let timer;

    const show = (index) => {
        current = (index + slides.length) % slides.length;

        slides.forEach((slide, i) => {
            const isActive = i === current;
            slide.classList.toggle('opacity-100', isActive);
            slide.classList.toggle('opacity-0', !isActive);
            slide.classList.toggle('is-active', isActive);
            slide.setAttribute('aria-hidden', String(!isActive));
            slide.inert = !isActive;
        });

        dots.forEach((dot, i) => {
            dot.classList.toggle('is-active', i === current);
            dot.setAttribute('aria-current', String(i === current));
        });
    };

    const start = () => {
        if (!autoplay) {
            return;
        }

        clearInterval(timer);
        slider.classList.remove('is-paused');
        const activeDot = dots[current];
        if (activeDot) {
            replayClass(activeDot, 'is-active');
        }
        timer = setInterval(() => {
            show(current + 1);
        }, INTERVAL);
    };

    const pause = () => {
        clearInterval(timer);
        slider.classList.add('is-paused');
    };

    const go = (index) => {
        show(index);
        start();
    };

    dots.forEach((dot, i) => dot.addEventListener('click', () => go(i)));
    slider.querySelector('[data-slide-prev]')?.addEventListener('click', () => go(current - 1));
    slider.querySelector('[data-slide-next]')?.addEventListener('click', () => go(current + 1));

    slider.addEventListener('focusin', (event) => {
        if (event.target.matches(':focus-visible')) {
            pause();
        }
    });
    slider.addEventListener('focusout', start);

    document.addEventListener('visibilitychange', () => (document.hidden ? pause() : start()));

    let touchStartX = null;
    let swiped = false;

    slider.addEventListener(
        'touchstart',
        (event) => {
            touchStartX = event.touches[0].clientX;
            swiped = false;
            pause();
        },
        { passive: true },
    );

    slider.addEventListener(
        'touchend',
        (event) => {
            if (touchStartX === null) {
                return;
            }

            const deltaX = event.changedTouches[0].clientX - touchStartX;
            touchStartX = null;

            if (Math.abs(deltaX) > SWIPE_THRESHOLD) {
                swiped = true;
                go(deltaX < 0 ? current + 1 : current - 1);
            } else {
                start();
            }
        },
        { passive: true },
    );

    slider.addEventListener(
        'click',
        (event) => {
            if (swiped) {
                event.preventDefault();
                swiped = false;
            }
        },
        true,
    );

    slider.classList.toggle('is-playing', autoplay);
    requestAnimationFrame(() => {
        show(0);
        start();
    });
}
