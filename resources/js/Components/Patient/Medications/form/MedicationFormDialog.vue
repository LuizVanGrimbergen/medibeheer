<script setup lang="ts">
import { computed, ref, toRef, useSlots, watch } from 'vue';
import MobileShellFormDialog from '@/Components/shell/MobileShellFormDialog.vue';
import MedicationFormDialogFooter from '@/Components/Patient/Medications/form/MedicationFormDialogFooter.vue';
import type {
    MedicationCreateFormWithErrors,
    MedicationFormWizardStep,
} from '@/Components/Patient/Medications/form/MedicationFormTypes';
import MedicationFormWizardSteps from '@/Components/Patient/Medications/form/MedicationFormWizardSteps.vue';
import { useMedicationFormWizard } from '@/Components/Patient/Medications/form/useMedicationFormWizard';
import { DialogDescription } from '@/Components/ui/dialog';
import { usePatientFormWizardStepMotion } from '@/composables/motion/usePatientFormWizardStepMotion';
import { mobileShellDialogDescriptionClass } from '@/lib/shell/mobileShellDialogLayout';

const props = withDefaults(
    defineProps<{
        open: boolean;
        title: string;
        formId: string;
        idPrefix: string;
        form: MedicationCreateFormWithErrors;
        dialogContentClass: string;
        startAtSummary?: boolean;
        showStockFields?: boolean;
        summaryHeading?: string;
    }>(),
    {
        startAtSummary: false,
        showStockFields: true,
    },
);

const slots = useSlots();

const emit = defineEmits<{
    'update:open': [value: boolean];
    submit: [];
    cancel: [];
    currentStepChange: [step: MedicationFormWizardStep];
}>();

const {
    currentStep,
    medicationProgressLabel,
    handleSubmit,
    handleMedicationFormFooterBack,
    goToMedicationWizardStepFromSummary,
    resetWizardToInitialStep,
} = useMedicationFormWizard({
    open: () => props.open,
    form: () => props.form,
    idPrefix: () => props.idPrefix,
    initialStep: () => (props.startAtSummary ? 7 : 1),
    onSubmit: () => {
        emit('submit');
    },
});

const showWizardProgress = computed(
    () => !props.startAtSummary || currentStep.value !== 7,
);

const dialogTitle = computed(() => {
    if (
        props.summaryHeading !== undefined &&
        props.summaryHeading !== '' &&
        currentStep.value === 7
    ) {
        return props.summaryHeading;
    }

    return props.title;
});

const showSummaryFooter = computed(
    () => currentStep.value === 7 && slots.summaryFooter !== undefined,
);

const isOpen = toRef(() => props.open);
const progressLabelRef = ref<HTMLElement | null>(null);

const { wizardStepPanelRef } = usePatientFormWizardStepMotion(
    currentStep,
    isOpen,
    { progressLabelRef },
);

watch(
    () => props.startAtSummary,
    (shouldStartAtSummary) => {
        if (!shouldStartAtSummary) {
            return;
        }

        resetWizardToInitialStep();
    },
    { immediate: true },
);

watch(
    currentStep,
    (step) => {
        emit('currentStepChange', step);
    },
    { immediate: true },
);

function handleDialogCancel(): void {
    emit('update:open', false);
    emit('cancel');
}
</script>

<template>
    <MobileShellFormDialog
        :open="props.open"
        :title="dialogTitle"
        :form-id="props.formId"
        :dialog-content-class="props.dialogContentClass"
        :step-key="currentStep"
        @update:open="emit('update:open', $event)"
        @submit="handleSubmit"
        @cancel="handleDialogCancel"
    >
        <template v-if="showWizardProgress" #description>
            <DialogDescription
                ref="progressLabelRef"
                :class="mobileShellDialogDescriptionClass"
                aria-live="polite"
            >
                {{ medicationProgressLabel }}
            </DialogDescription>
        </template>

        <div ref="wizardStepPanelRef">
            <MedicationFormWizardSteps
                :current-step="currentStep"
                :form="props.form"
                :id-prefix="props.idPrefix"
                :show-stock-fields="props.showStockFields"
                :go-to-wizard-step-from-summary="
                    goToMedicationWizardStepFromSummary
                "
            />
        </div>

        <template #footer>
            <slot
                v-if="showSummaryFooter"
                name="summaryFooter"
                :processing="props.form.processing"
            />
            <MedicationFormDialogFooter
                v-else
                :current-step="currentStep"
                :processing="props.form.processing"
                @cancel="handleDialogCancel"
                @back="handleMedicationFormFooterBack"
            />
        </template>
    </MobileShellFormDialog>
</template>
