import { router } from '@inertiajs/vue3';
import { onUnmounted, ref } from 'vue';
import type {
    InertiaVisitOptions,
    LoadingScreenMessageKey,
} from '@/lib/navigation/inertiaLoadingScreenPolicy';
import {
    loadingScreenShowDelayMs,
    resolveLoadingScreenMessageKey,
    shouldShowLoadingScreenForVisit,
} from '@/lib/navigation/inertiaLoadingScreenPolicy';

export function useInertiaNavigationLoading() {
    const isLoading = ref(false);
    const loadingMessageKey = ref<LoadingScreenMessageKey>('default');
    let showTimeoutId: ReturnType<typeof globalThis.setTimeout> | null = null;
    let trackedVisitCount = 0;

    const clearShowTimeout = (): void => {
        if (showTimeoutId === null) {
            return;
        }

        globalThis.clearTimeout(showTimeoutId);
        showTimeoutId = null;
    };

    const scheduleShow = (): void => {
        clearShowTimeout();

        showTimeoutId = globalThis.setTimeout(() => {
            showTimeoutId = null;

            if (trackedVisitCount > 0) {
                isLoading.value = true;
            }
        }, loadingScreenShowDelayMs());
    };

    const finishTrackedVisit = (): void => {
        trackedVisitCount = Math.max(0, trackedVisitCount - 1);

        if (trackedVisitCount > 0) {
            return;
        }

        clearShowTimeout();
        isLoading.value = false;
    };

    const removeStartListener = router.on('start', (event) => {
        const visit = event.detail.visit as InertiaVisitOptions;

        if (!shouldShowLoadingScreenForVisit(visit)) {
            return;
        }

        loadingMessageKey.value = resolveLoadingScreenMessageKey(visit);
        trackedVisitCount += 1;

        if (trackedVisitCount === 1) {
            scheduleShow();
        }
    });

    const removeFinishListener = router.on('finish', () => {
        finishTrackedVisit();
    });

    const removeCancelListener = router.on('cancel', () => {
        finishTrackedVisit();
    });

    onUnmounted(() => {
        clearShowTimeout();
        removeStartListener();
        removeFinishListener();
        removeCancelListener();
    });

    return { isLoading, loadingMessageKey };
}
