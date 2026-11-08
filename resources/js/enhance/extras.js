import { prefersReducedMotion } from '../modules/motion';

/**
 * `[data-auto-animate]`: children animate in/out/move whenever the list
 * changes (Livewire re-renders, filters, cart rows).
 */
export async function enhanceAutoAnimate(lists) {
    const { default: autoAnimate } = await import('@formkit/auto-animate');

    lists.forEach((list) => autoAnimate(list, { duration: 250, easing: 'ease-out', disrespectUserMotionPreference: false }));
}

/**
 * `<canvas data-lottie-src="/animations/x.lottie">`: plays a dotLottie/Lottie
 * file once it scrolls into view. The WASM renderer is served from our own
 * build output, never a CDN.
 */
export async function enhanceLottie(canvases) {
    const [{ DotLottie }, { default: wasmUrl }] = await Promise.all([
        import('@lottiefiles/dotlottie-web'),
        import('@lottiefiles/dotlottie-web/dotlottie-player.wasm?url'),
    ]);

    DotLottie.setWasmUrl(wasmUrl);
    const reduced = prefersReducedMotion();

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            const canvas = entry.target;
            if (!entry.isIntersecting) {
                canvas.lottie?.pause();

                return;
            }

            if (!canvas.lottie) {
                canvas.lottie = new DotLottie({
                    canvas,
                    src: canvas.dataset.lottieSrc,
                    loop: canvas.dataset.lottieLoop !== 'false',
                    autoplay: !reduced,
                });

                return;
            }

            if (!reduced) {
                canvas.lottie.play();
            }
        });
    });

    canvases.forEach((canvas) => observer.observe(canvas));
}

/**
 * Confetti burst. Fire it from `[data-confetti]` clicks, on load with
 * `[data-confetti-on-load]` (e.g. order success), or from a Livewire/JS
 * `celebrate` window event.
 */
export async function enhanceConfetti() {
    const { default: confetti } = await import('canvas-confetti');
    const colors = ['#df0100', '#ffffff', '#1f1f1f', '#ff5a52'];

    const burst = (origin = { x: 0.5, y: 0.6 }) => confetti({
        particleCount: 90,
        spread: 75,
        startVelocity: 38,
        origin,
        colors,
        disableForReducedMotion: true,
    });

    document.addEventListener('click', (event) => {
        const trigger = event.target instanceof Element ? event.target.closest('[data-confetti]') : null;
        if (!trigger) {
            return;
        }

        const rect = trigger.getBoundingClientRect();
        burst({ x: (rect.left + rect.width / 2) / window.innerWidth, y: (rect.top + rect.height / 2) / window.innerHeight });
    });

    window.addEventListener('celebrate', () => burst());

    if (document.querySelector('[data-confetti-on-load]')) {
        burst();
    }
}
