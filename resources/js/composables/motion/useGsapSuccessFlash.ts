import {
    animateSuccessFlashOverlay,
    resetSuccessFlashOverlay,
} from '@/lib/motion/gsapMotion';
import { resolveGsapTargetElement } from '@/lib/motion/resolveGsapTargetElement';
import {
    nextTick,
    onMounted,
    onUnmounted,
    watch,
    type ComponentPublicInstance,
    type Ref,
} from 'vue';

export function useGsapSuccessFlash(
    overlayRef: Ref<HTMLElement | ComponentPublicInstance | null>,
    shouldFlash: Ref<boolean>,
): void {
    let tween: ReturnType<typeof animateSuccessFlashOverlay> | null = null;
    let retryFrameId: number | null = null;

    const cancelRetry = (): void => {
        if (retryFrameId === null) {
            return;
        }

        globalThis.cancelAnimationFrame(retryFrameId);
        retryFrameId = null;
    };

    const applyAnimation = (): boolean => {
        const element = resolveGsapTargetElement(overlayRef.value);

        if (element === null) {
            return false;
        }

        tween?.kill();
        tween = animateSuccessFlashOverlay(element);

        return true;
    };

    const scheduleAnimation = async (): Promise<void> => {
        cancelRetry();

        await nextTick();

        if (applyAnimation()) {
            return;
        }

        retryFrameId = globalThis.requestAnimationFrame(() => {
            retryFrameId = null;
            applyAnimation();
        });
    };

    watch(
        shouldFlash,
        (flash, previousFlash) => {
            if (!flash || previousFlash === true) {
                return;
            }

            void scheduleAnimation();
        },
        { flush: 'post' },
    );

    onMounted(() => {
        if (shouldFlash.value) {
            void scheduleAnimation();
        }
    });

    onUnmounted(() => {
        cancelRetry();
        tween?.kill();
        tween = null;

        const element = resolveGsapTargetElement(overlayRef.value);

        if (element !== null) {
            resetSuccessFlashOverlay(element);
        }
    });
}
