import { prefersReducedMotion, replayClass } from './motion';

const URGENT_SECONDS = 3600;

const serverOffset = (() => {
    const serverNow = Date.parse(document.body.dataset.serverNow ?? '');

    return Number.isNaN(serverNow) ? 0 : serverNow - Date.now();
})();

const pad = (value) => String(value).padStart(2, '0');

/**
 * Smooth roll-down digit animation with crisp easing and no blank flicker.
 */
function rollTo(cell, text, isInitial = false) {
    if (isInitial || prefersReducedMotion() || typeof cell.animate !== 'function') {
        cell.textContent = text;
        cell.dataset.value = text;

        return;
    }

    cell.dataset.value = text;
    cell.textContent = text;

    cell.getAnimations().forEach((animation) => animation.cancel());

    cell.animate(
        [
            { transform: 'translateY(-5px) scale(1.06)', opacity: 0.75 },
            { transform: 'translateY(0) scale(1)', opacity: 1 },
        ],
        {
            duration: 220,
            easing: 'cubic-bezier(0.16, 1, 0.3, 1)',
            fill: 'forwards',
        },
    );

    if (cell.dataset.unit === 'seconds') {
        const parent = cell.closest('.countdown-sec');
        if (parent) {
            parent.classList.remove('is-ticking');
            void parent.offsetWidth;
            parent.classList.add('is-ticking');
        }
    }
}

/**
 * Tick every `[data-countdown]` against server time. Changed digits smoothly roll in,
 * and the last hour switches the timer to its urgent style.
 */
export function initCountdowns() {
    const countdowns = [...document.querySelectorAll('[data-countdown]')];
    if (countdowns.length === 0) {
        return;
    }

    let isInitial = true;

    const tick = () => {
        const now = Date.now() + serverOffset;

        countdowns.forEach((countdown) => {
            const endsAt = Date.parse(countdown.dataset.endsAt);
            if (Number.isNaN(endsAt)) {
                return;
            }

            const remaining = Math.max(0, endsAt - now);
            const seconds = Math.floor(remaining / 1000);
            const parts = {
                days: Math.floor(seconds / 86400),
                hours: Math.floor(seconds / 3600) % 24,
                minutes: Math.floor(seconds / 60) % 60,
                seconds: seconds % 60,
            };

            const isUrgent = seconds > 0 && seconds <= URGENT_SECONDS;
            if (isUrgent && !countdown.classList.contains('is-urgent')) {
                replayClass(countdown, 'animate-shake');
            }
            countdown.classList.toggle('is-urgent', isUrgent);

            Object.entries(parts).forEach(([unit, value]) => {
                const cell = countdown.querySelector(`[data-unit="${unit}"]`);
                const text = pad(value);
                if (cell && (isInitial || (cell.dataset.value ?? cell.textContent) !== text)) {
                    rollTo(cell, text, isInitial);
                }
            });
        });

        isInitial = false;
    };

    const scheduleTick = () => {
        tick();
        setTimeout(scheduleTick, 1000 - ((Date.now() + serverOffset) % 1000) + 5);
    };

    scheduleTick();
}

