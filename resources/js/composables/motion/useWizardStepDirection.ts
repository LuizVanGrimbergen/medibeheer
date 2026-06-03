import type { Ref } from 'vue';
import { ref, watch } from 'vue';
import type { WizardStepDirection } from '@/lib/motion/gsapMotion';

export function useWizardStepDirection(currentStep: Ref<number>): {
    stepDirection: Ref<WizardStepDirection>;
} {
    const stepDirection = ref<WizardStepDirection>('forward');

    watch(currentStep, (newStep, oldStep) => {
        if (oldStep === undefined) {
            return;
        }

        stepDirection.value = newStep > oldStep ? 'forward' : 'backward';
    });

    return {
        stepDirection,
    };
}
