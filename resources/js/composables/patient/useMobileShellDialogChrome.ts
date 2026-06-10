import { router } from '@inertiajs/vue3';
import {
    computed,
    onScopeDispose,
    onUnmounted,
    ref,
    watch,
} from 'vue';
import type { Ref } from 'vue';

const mobileShellDialogChromeOpenCount = ref(0);
const mobileShellPageChromeOpenCount = ref(0);

let navigationResetRegistered = false;

/** True while a mobile shell dialog or full-page wizard should hide the footer nav. */
export const isMobileShellFooterHidden = computed(
    () =>
        mobileShellDialogChromeOpenCount.value > 0 ||
        mobileShellPageChromeOpenCount.value > 0,
);

function setMobileShellDialogChromeOpen(open: boolean): void {
    if (open) {
        mobileShellDialogChromeOpenCount.value += 1;

        return;
    }

    mobileShellDialogChromeOpenCount.value = Math.max(
        0,
        mobileShellDialogChromeOpenCount.value - 1,
    );
}

function setMobileShellPageChromeOpen(open: boolean): void {
    if (open) {
        mobileShellPageChromeOpenCount.value += 1;

        return;
    }

    mobileShellPageChromeOpenCount.value = Math.max(
        0,
        mobileShellPageChromeOpenCount.value - 1,
    );
}

/** Clears stale dialog footer-hide state after Inertia page transitions. */
export function resetMobileShellDialogChromeOpenCount(): void {
    mobileShellDialogChromeOpenCount.value = 0;
}

function registerMobileShellChromeNavigationReset(): void {
    if (navigationResetRegistered || globalThis.window === undefined) {
        return;
    }

    navigationResetRegistered = true;

    router.on('finish', () => {
        resetMobileShellDialogChromeOpenCount();
    });
}

registerMobileShellChromeNavigationReset();

/** Keeps {@link isMobileShellFooterHidden} in sync for modal shell dialogs. */
export function useMobileShellDialogChromeSync(open: Ref<boolean>): void {
    let contributed = false;

    watch(
        open,
        (isOpen, wasOpen) => {
            if (isOpen === wasOpen) {
                return;
            }

            if (isOpen) {
                if (!contributed) {
                    contributed = true;
                    setMobileShellDialogChromeOpen(true);
                }

                return;
            }

            if (contributed) {
                contributed = false;
                setMobileShellDialogChromeOpen(false);
            }
        },
        { immediate: true },
    );

    onScopeDispose(() => {
        if (!contributed) {
            return;
        }

        contributed = false;
        setMobileShellDialogChromeOpen(false);
    });
}

/** Resets the patient layout scroll column to the top on full Inertia visits. */
export function useMobileShellMainScrollReset(
    scrollRef: Ref<HTMLElement | null>,
): void {
    const removeListener = router.on('start', (event) => {
        if (event.detail.visit.preserveScroll === true) {
            return;
        }

        scrollRef.value?.scrollTo({ top: 0, left: 0 });
    });

    onUnmounted(() => {
        removeListener();
    });
}

/** Hides the footer nav for the lifetime of a full-page shell wizard route. */
export function useMobileShellPageChrome(): void {
    let contributed = false;

    const show = (): void => {
        if (contributed) {
            return;
        }

        contributed = true;
        setMobileShellPageChromeOpen(true);
    };

    const hide = (): void => {
        if (!contributed) {
            return;
        }

        contributed = false;
        setMobileShellPageChromeOpen(false);
    };

    show();

    onScopeDispose(() => {
        hide();
    });
}
