import { useGsapWizardProgressLabel } from '@/composables/motion/useGsapWizardProgressLabel';
import { useGsapWizardStepEnter } from '@/composables/motion/useGsapWizardStepEnter';
import { useWizardStepDirection } from '@/composables/motion/useWizardStepDirection';
import { ref, type ComponentPublicInstance, type Ref } from 'vue';

type UsePatientFormWizardStepMotionOptions = {
    progressLabelRef?: Ref<HTMLElement | ComponentPublicInstance | null>;
};

export function usePatientFormWizardStepMotion(
    stepIndex: Ref<number>,
    isOpen: Ref<boolean>,
    options: UsePatientFormWizardStepMotionOptions = {},
) {
    const wizardStepPanelRef = ref<HTMLElement | null>(null);
    const { stepDirection } = useWizardStepDirection(stepIndex);

    useGsapWizardStepEnter(
        wizardStepPanelRef,
        stepIndex,
        stepDirection,
        isOpen,
    );

    if (options.progressLabelRef !== undefined) {
        useGsapWizardProgressLabel(
            options.progressLabelRef,
            stepIndex,
            isOpen,
        );
    }

    return { wizardStepPanelRef };
}
