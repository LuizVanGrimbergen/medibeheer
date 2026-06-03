import {
    animateCheckmarkDraw,
    resetCheckmarkDraw,
} from '@/lib/motion/gsapMotion';
import { resolveGsapTargetElement } from '@/lib/motion/resolveGsapTargetElement';
import {
    nextTick,
    onUnmounted,
    watch,
    type ComponentPublicInstance,
    type Ref,
} from 'vue';

export function useGsapCheckmarkDraw(
    targetRef: Ref<HTMLElement | ComponentPublicInstance | null>,
    shouldAnimate: Ref<boolean>,
): void {
    let timeline: ReturnType<typeof animateCheckmarkDraw> | null = null;
    let retryFrameId: number | null = null;

    const cancelRetry = (): void => {
        if (retryFrameId === null) {
            return;
        }

        globalThis.cancelAnimationFrame(retryFrameId);
        retryFrameId = null;
    };

    const applyAnimation = (): boolean => {
        const element = resolveGsapTargetElement(targetRef.value);

        if (element === null) {
            return false;
        }

        timeline?.kill();
        timeline = animateCheckmarkDraw(element);

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
