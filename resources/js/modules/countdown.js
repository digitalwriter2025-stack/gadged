import { digits } from './i18n';
import { prefersReducedMotion, replayClass } from './motion';

const URGENT_SECONDS = 3600;

const serverOffset = (() => {
    const serverNow = Date.parse(document.body.dataset.serverNow ?? '');

    return Number.isNaN(serverNow) ? 0 : serverNow - Date.now();
})();

const pad = (value) => String(value).padStart(2, '0');

const digitSpan = (text, className) => {
    const span = document.createElement('span');
    span.className = className;
    span.textContent = text;

    return span;
};

/**
 * Cross-fade a cell to its new value: the old digits drift down and fade out while
 * the new ones drop in from above, so every tick reads as a soft roll-down.
 */
function rollTo(cell, text, isInitial = false) {
    const previous = cell.dataset.value;
    cell.dataset.value = text;

    if (isInitial || previous === undefined || prefersReducedMotion() || typeof cell.animate !== 'function') {
        cell.textContent = text;

        return;
    }

    const entering = digitSpan(text, 'countdown-digit');
    const leaving = digitSpan(previous, 'countdown-digit is-leaving');
    leaving.setAttribute('aria-hidden', 'true');
    cell.replaceChildren(entering, leaving);

    entering.animate(
        [
            { opacity: 0, transform: 'translateY(-0.55em) scale(0.9)' },
            { opacity: 1, transform: 'none' },
        ],
        { duration: 650, delay: 250, easing: 'cubic-bezier(0.25, 1, 0.5, 1)', fill: 'backwards' },
    );

    leaving.animate(
        [
            { opacity: 1, transform: 'none' },
            { opacity: 0, transform: 'translateY(0.55em) scale(0.9)' },
        ],
        { duration: 450, easing: 'cubic-bezier(0.45, 0, 0.55, 1)', fill: 'forwards' },
    ).onfinish = () => leaving.remove();

    if (cell.dataset.unit === 'seconds') {
        const box = cell.closest('.countdown-sec');
        if (box) {
            replayClass(box, 'is-ticking');
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
                const text = digits(pad(value));
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

