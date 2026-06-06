import { router } from '@inertiajs/vue3';
import {
    computed,
    onScopeDispose,
    onUnmounted,
    ref,
    watch,
    type Ref,
} from 'vue';

const patientShellDialogChromeOpenCount = ref(0);
const patientShellPageChromeOpenCount = ref(0);

let navigationResetRegistered = false;

/** True while a patient shell dialog or full-page wizard should hide the footer nav. */
export const isPatientShellFooterHidden = computed(
    () =>
        patientShellDialogChromeOpenCount.value > 0 ||
        patientShellPageChromeOpenCount.value > 0,
);

function setPatientShellDialogChromeOpen(open: boolean): void {
    if (open) {
        patientShellDialogChromeOpenCount.value += 1;

        return;
    }

    patientShellDialogChromeOpenCount.value = Math.max(
        0,
        patientShellDialogChromeOpenCount.value - 1,
    );
}

function setPatientShellPageChromeOpen(open: boolean): void {
    if (open) {
        patientShellPageChromeOpenCount.value += 1;

        return;
    }

    patientShellPageChromeOpenCount.value = Math.max(
        0,
        patientShellPageChromeOpenCount.value - 1,
    );
}

/** Clears stale dialog footer-hide state after Inertia page transitions. */
export function resetPatientShellDialogChromeOpenCount(): void {
    patientShellDialogChromeOpenCount.value = 0;
}

function registerPatientShellChromeNavigationReset(): void {
    if (navigationResetRegistered || globalThis.window === undefined) {
        return;
    }

    navigationResetRegistered = true;

    router.on('finish', () => {
        resetPatientShellDialogChromeOpenCount();
    });
}

registerPatientShellChromeNavigationReset();

/** Keeps {@link isPatientShellFooterHidden} in sync for modal shell dialogs. */
export function usePatientShellDialogChromeSync(open: Ref<boolean>): void {
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
                    setPatientShellDialogChromeOpen(true);
                }

                return;
            }

            if (contributed) {
                contributed = false;
                setPatientShellDialogChromeOpen(false);
            }
        },
        { immediate: true },
    );

    onScopeDispose(() => {
        if (!contributed) {
            return;
        }

        contributed = false;
        setPatientShellDialogChromeOpen(false);
    });
}

/** Resets the patient layout scroll column to the top on full Inertia visits. */
export function usePatientShellMainScrollReset(
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
export function usePatientShellPageChrome(): void {
    let contributed = false;

    const show = (): void => {
        if (contributed) {
            return;
        }

        contributed = true;
        setPatientShellPageChromeOpen(true);
    };

    const hide = (): void => {
        if (!contributed) {
            return;
        }

        contributed = false;
        setPatientShellPageChromeOpen(false);
    };

    show();

    onScopeDispose(() => {
        hide();
    });
}
