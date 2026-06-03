import type { GsapTween } from '@/lib/motion/gsapMotion';
import {
    animateSuccessFlashOverlay,
    resetSuccessFlashOverlay,
} from '@/lib/motion/gsapMotion';
import { resolveGsapTargetElement } from '@/lib/motion/resolveGsapTargetElement';
import type { ComponentPublicInstance, Ref } from 'vue';
import { nextTick, onMounted, onUnmounted, watch } from 'vue';

export function useGsapSuccessFlash(
    overlayRef: Ref<HTMLElement | ComponentPublicInstance | null>,
    shouldFlash: Ref<boolean>,
): void {
    let tween: GsapTween | null = null;
    let retryFrameId: number | null = null;

    const cancelRetry = (): void => {
        if (retryFrameId === null) {
            return;
        }

        globalThis.cancelAnimationFrame(retryFrameId);
        retryFrameId = null;
    };

    const applyAnimation = async (): Promise<boolean> => {
        const element = resolveGsapTargetElement(overlayRef.value);

        if (element === null) {
            return false;
        }

        tween?.kill();
        tween = await animateSuccessFlashOverlay(element);

        return true;
    };

    const scheduleAnimation = async (): Promise<void> => {
        cancelRetry();

        await nextTick();

        if (await applyAnimation()) {
            return;
        }

        retryFrameId = globalThis.requestAnimationFrame(() => {
            retryFrameId = null;
            void applyAnimation();
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
