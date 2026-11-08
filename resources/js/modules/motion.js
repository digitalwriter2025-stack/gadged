export const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/**
 * Restart a CSS animation class on an element.
 */
export function replayClass(element, className) {
    element.classList.remove(className);
    void element.offsetWidth;
    element.classList.add(className);
}
