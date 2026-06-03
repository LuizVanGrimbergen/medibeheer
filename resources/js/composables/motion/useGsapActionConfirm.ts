import type { GsapTween } from '@/lib/motion/gsapMotion';
import {
    animateActionConfirm,
    resetActionConfirmVisibility,
} from '@/lib/motion/gsapMotion';
import { resolveGsapTargetElement } from '@/lib/motion/resolveGsapTargetElement';
import type { ComponentPublicInstance, Ref } from 'vue';
import { nextTick, onUnmounted, watch } from 'vue';

export function useGsapActionConfirm(
    targetRef: Ref<HTMLElement | ComponentPublicInstance | null>,
    shouldAnimate: Ref<boolean>,
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
        const element = resolveGsapTargetElement(targetRef.value);

        if (element === null) {
            return false;
        }

        tween?.kill();
        tween = await animateActionConfirm(element);

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
        shouldAnimate,
        (active) => {
            if (!active) {
                cancelRetry();

                return;
            }

            void scheduleAnimation();
        },
        { immediate: true, flush: 'post' },
    );

    onUnmounted(() => {
        cancelRetry();
        tween?.kill();
        tween = null;

        const element = resolveGsapTargetElement(targetRef.value);

        if (element !== null) {
            resetActionConfirmVisibility(element);
        }
    });
}
