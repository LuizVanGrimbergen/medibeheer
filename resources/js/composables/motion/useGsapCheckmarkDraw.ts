import type { ComponentPublicInstance, Ref } from 'vue';
import { nextTick, onUnmounted, watch } from 'vue';
import type { GsapTimeline } from '@/lib/motion/gsapMotion';
import {
    animateCheckmarkDraw,
    resetCheckmarkDraw,
} from '@/lib/motion/gsapMotion';
import { resolveGsapTargetElement } from '@/lib/motion/resolveGsapTargetElement';

export function useGsapCheckmarkDraw(
    targetRef: Ref<HTMLElement | ComponentPublicInstance | null>,
    shouldAnimate: Ref<boolean>,
): void {
    let timeline: GsapTimeline | null = null;
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

        timeline?.kill();
        timeline = await animateCheckmarkDraw(element);

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
        timeline?.kill();
        timeline = null;

        const element = resolveGsapTargetElement(targetRef.value);

        if (element !== null) {
            resetCheckmarkDraw(element);
        }
    });
}
