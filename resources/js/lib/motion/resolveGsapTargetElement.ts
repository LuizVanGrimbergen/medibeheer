import type { ComponentPublicInstance } from 'vue';

export function resolveGsapTargetElement(
    target: HTMLElement | ComponentPublicInstance | null,
): HTMLElement | null {
    if (target === null) {
        return null;
    }

    if (target instanceof HTMLElement) {
        return target;
    }

    const element = target.$el;

    if (element instanceof HTMLElement) {
        return element;
    }

    return null;
}
