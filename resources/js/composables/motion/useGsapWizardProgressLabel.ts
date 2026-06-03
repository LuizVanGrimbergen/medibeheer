import { useGsapActionConfirm } from '@/composables/motion/useGsapActionConfirm';
import { resetActionConfirmVisibility } from '@/lib/motion/gsapMotion';
import { resolveGsapTargetElement } from '@/lib/motion/resolveGsapTargetElement';
import {
    nextTick,
    onUnmounted,
    ref,
    watch,
    type ComponentPublicInstance,
    type Ref,
} from 'vue';

export function useGsapWizardProgressLabel(
    progressLabelRef: Ref<HTMLElement | ComponentPublicInstance | null>,
    stepIndex: Ref<number>,
    isOpen: Ref<boolean>,
): void {
    const motionActive = ref(false);
    const skipNextTransition = ref(false);

    useGsapActionConfirm(progressLabelRef, motionActive);

    watch(
        stepIndex,
        () => {
            if (!isOpen.value || skipNextTransition.value) {
                return;
            }

            motionActive.value = true;
        },
        { flush: 'post' },
    );

    watch(motionActive, (active) => {
        if (!active) {
            return;
        }

        void nextTick(() => {
            motionActive.value = false;
        });
    });

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

            skipNextTransition.value = false;
        },
        { flush: 'post' },
    );

    onUnmounted(() => {
        const element = resolveGsapTargetElement(progressLabelRef.value);

        if (element !== null) {
            resetActionConfirmVisibility(element);
        }
    });
}
