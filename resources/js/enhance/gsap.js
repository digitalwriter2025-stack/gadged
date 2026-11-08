import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';

gsap.registerPlugin(ScrollTrigger, SplitText);

const REVEAL = { from: { autoAlpha: 0, y: 32 }, to: { autoAlpha: 1, y: 0, duration: 0.8, ease: 'expo.out', stagger: 0.07 } };

/**
 * Scroll reveals via ScrollTrigger.batch. Takes over from the CSS/IO version:
 * `reveal-gsap` on <html> neutralises the CSS hidden state in the same frame
 * GSAP hides the elements, so nothing flashes.
 */
function initReveals() {
    const items = gsap.utils.toArray('[data-reveal]');
    if (items.length === 0) {
        return;
    }

    document.documentElement.classList.add('reveal-gsap');
    gsap.set(items, REVEAL.from);

    ScrollTrigger.batch(items, {
        start: 'top 92%',
        once: true,
        onEnter: (batch) => {
            batch.forEach((item) => item.classList.add('is-visible'));
            gsap.to(batch, { ...REVEAL.to, overwrite: true });
        },
    });
}

/**
 * Section headings rise in word by word.
 */
function initSplitHeadings() {
    document.querySelectorAll('[data-split]').forEach((heading) => {
        SplitText.create(heading, {
            type: 'words',
            wordsClass: 'split-word',
            mask: 'words',
            autoSplit: true,
            onSplit: (split) => gsap.from(split.words, {
                yPercent: 110,
                duration: 0.9,
                ease: 'expo.out',
                stagger: 0.06,
                scrollTrigger: { trigger: heading, start: 'top 92%', once: true },
            }),
        });
    });
}

/**
 * Banner images drift slower than the page (desktop only).
 */
function initParallax() {
    gsap.utils.toArray('[data-parallax]').forEach((image) => {
        const depth = Number(image.dataset.parallax) || 8;

        gsap.fromTo(
            image,
            { yPercent: -depth, scale: 1 + (depth * 2) / 100 },
            {
                yPercent: depth,
                ease: 'none',
                scrollTrigger: { trigger: image.parentElement, start: 'top bottom', end: 'bottom top', scrub: true },
            },
        );
    });
}

/**
 * Flash-sale "View Deal" buttons breathe slowly while on screen, each on its
 * own rhythm so the row never pulses in lockstep.
 */
function initDealPulse() {
    const buttons = gsap.utils.toArray('[data-deal-pulse]');
    if (buttons.length === 0) {
        return;
    }

    const pulses = buttons.map((button) => {
        const bolt = button.querySelector('.animate-bolt');
        if (bolt) {
            bolt.style.animationDuration = `${gsap.utils.random(2.2, 3.4, 0.1)}s`;
            bolt.style.animationDelay = `-${gsap.utils.random(0, 3, 0.1)}s`;
        }

        return gsap.to(button, {
            scale: 1.03,
            duration: gsap.utils.random(1.6, 2.6, 0.1),
            delay: gsap.utils.random(0, 1.5, 0.1),
            ease: 'sine.inOut',
            repeat: -1,
            yoyo: true,
            paused: true,
        });
    });

    ScrollTrigger.create({
        trigger: buttons[0].closest('section') ?? buttons[0],
        start: 'top bottom',
        end: 'bottom top',
        onToggle: (self) => pulses.forEach((pulse) => (self.isActive ? pulse.play() : pulse.pause())),
    });
}

/**
 * Smooth wheel scrolling for mouse/trackpad users, driven by GSAP's ticker so
 * ScrollTrigger stays in sync.
 */
async function initSmoothScroll() {
    const [{ default: Lenis }, { setSmoothScroller }] = await Promise.all([
        import('lenis'),
        import('../modules/scroll'),
    ]);

    const lenis = new Lenis({
        autoRaf: false,
        anchors: true,
        prevent: (node) => Boolean(node.closest?.('dialog')),
    });

    lenis.on('scroll', ScrollTrigger.update);
    const tick = (time) => lenis.raf(time * 1000);
    gsap.ticker.add(tick);
    gsap.ticker.lagSmoothing(0);

    const drawerObserver = new MutationObserver(() => {
        document.querySelector('dialog[open]') ? lenis.stop() : lenis.start();
    });
    document.querySelectorAll('dialog').forEach((dialog) => drawerObserver.observe(dialog, { attributes: true, attributeFilter: ['open'] }));

    setSmoothScroller(lenis);

    return () => {
        setSmoothScroller(null);
        drawerObserver.disconnect();
        gsap.ticker.remove(tick);
        lenis.destroy();
    };
}

export function enhanceMotion() {
    const mm = gsap.matchMedia();

    mm.add(
        {
            motion: '(prefers-reduced-motion: no-preference)',
            desktop: '(min-width: 1024px)',
            finePointer: '(pointer: fine)',
        },
        (context) => {
            const { motion, desktop, finePointer } = context.conditions;
            if (!motion) {
                document.documentElement.classList.remove('reveal-gsap');

                return;
            }

            initReveals();
            initSplitHeadings();
            initDealPulse();

            if (desktop) {
                initParallax();
            }

            let destroyLenis = null;
            if (finePointer) {
                initSmoothScroll().then((destroy) => {
                    destroyLenis = destroy;
                });
            }

            return () => {
                destroyLenis?.();
                document.documentElement.classList.remove('reveal-gsap');
            };
        },
    );

    window.addEventListener('load', () => ScrollTrigger.refresh(), { once: true });
}
