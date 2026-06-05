const COLLAPSIBLE_CONTENT_ANIMATION_MS = 300;

function prefersReducedMotion(): boolean {
    return globalThis.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

export function scrollExpandedSectionIntoView(
    element: HTMLElement | null | undefined,
): void {
    if (!(element instanceof HTMLElement)) {
        return;
    }

    const reducedMotion = prefersReducedMotion();
    const delay = reducedMotion ? 0 : COLLAPSIBLE_CONTENT_ANIMATION_MS;

    globalThis.setTimeout(() => {
        element.scrollIntoView({
            behavior: reducedMotion ? 'auto' : 'smooth',
            block: 'start',
        });
    }, delay);
}
