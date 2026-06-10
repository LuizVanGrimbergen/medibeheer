import type { Ref } from 'vue';
import { nextTick, watch } from 'vue';

export function scrollMobileShellWizardBodyToTop(
    scrollElement: HTMLElement | null,
): void {
    if (scrollElement === null) {
        return;
    }

    scrollElement.scrollTop = 0;
}

export function useMobileShellWizardScrollReset(
    scrollRef: Ref<HTMLElement | null>,
    stepKey: Ref<number | string | undefined>,
    active: Ref<boolean>,
): void {
    watch(
        () => [active.value, stepKey.value] as const,
        async ([isActive, key], previous) => {
            if (!isActive || key === undefined) {
                return;
            }

            const wasActive = previous?.[0] ?? false;
            const previousKey = previous?.[1];

            const openedDialog = isActive && !wasActive;
            const advancedStep =
                wasActive &&
                isActive &&
                previousKey !== undefined &&
                previousKey !== key;

            if (!openedDialog && !advancedStep) {
                return;
            }

            await nextTick();
            scrollMobileShellWizardBodyToTop(scrollRef.value);
        },
        { flush: 'post' },
    );
}
