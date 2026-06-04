const COLLAPSIBLE_CONTENT_ANIMATION_MS = 300;

function prefersReducedMotion(): boolean {
    return globalThis.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

export function scrollPatientListCardDetailsIntoView(
    element: HTMLElement | null | undefined,
): void {
    if (element === null || element === undefined) {
        return;
    }

    const reducedMotion = prefersReducedMotion();
    const delay = reducedMotion ? 0 : COLLAPSIBLE_CONTENT_ANIMATION_MS;

    globalThis.setTimeout(() => {
        element.scrollIntoView({
            behavior: reducedMotion ? 'auto' : 'smooth',
            block: 'nearest',
        });
    }, delay);
}
