import {
    animateWizardStepEnter,
    resetWizardStepEnterVisibility,
    type WizardStepDirection,
} from '@/lib/motion/gsapMotion';
import { resolveGsapTargetElement } from '@/lib/motion/resolveGsapTargetElement';
import {
    nextTick,
    onUnmounted,
    ref,
    watch,
    type ComponentPublicInstance,
    type Ref,
} from 'vue';

export function useGsapWizardStepEnter(
    targetRef: Ref<HTMLElement | ComponentPublicInstance | null>,
    currentStep: Ref<number>,
    stepDirection: Ref<WizardStepDirection>,
    isOpen: Ref<boolean>,
): void {
    let tween: ReturnType<typeof animateWizardStepEnter> | null = null;
    let retryFrameId: number | null = null;
    const skipNextTransition = ref(false);

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

        tween?.kill();
        tween = animateWizardStepEnter(element, stepDirection.value);

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

    const resetTargetVisibility = (): void => {
        const element = resolveGsapTargetElement(targetRef.value);

        if (element !== null) {
            resetWizardStepEnterVisibility(element);
        }
    };

    watch(
        currentStep,
        () => {
            if (!isOpen.value) {
                return;
            }

            if (skipNextTransition.value) {
                resetTargetVisibility();

                return;
            }

            void scheduleAnimation();
        },
        { flush: 'post' },
    );

    watch(
        isOpen,
        (open) => {
            if (open) {
                skipNextTransition.value = true;

                void nextTick(() => {
                    skipNextTransition.value = false;
                });

                return;
            }

            cancelRetry();
            tween?.kill();
            tween = null;
            skipNextTransition.value = false;
            resetTargetVisibility();
        },
        { flush: 'post' },
    );

    onUnmounted(() => {
        cancelRetry();
        tween?.kill();
        tween = null;
        resetTargetVisibility();
    });
}
