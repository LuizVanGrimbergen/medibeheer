<script setup lang="ts">
import MobileShellWizardCard from '@/Components/shell/MobileShellWizardCard.vue';
import type {
    MedicationCreateFormWithErrors,
    MedicationFormWizardStep,
} from '@/Components/Patient/Medications/form/MedicationFormTypes';
import MedicationCreateSummaryStep from '@/Components/Patient/Medications/steps/MedicationCreateSummaryStep.vue';
import MedicationDetailsStep from '@/Components/Patient/Medications/steps/MedicationDetailsStep.vue';
import MedicationNoteStep from '@/Components/Patient/Medications/steps/MedicationNoteStep.vue';
import MedicationScheduleDoseTimesStep from '@/Components/Patient/Medications/steps/MedicationScheduleDoseTimesStep.vue';
import MedicationScheduleDurationStep from '@/Components/Patient/Medications/steps/MedicationScheduleDurationStep.vue';
import MedicationScheduleMealsAndFrequencyStep from '@/Components/Patient/Medications/steps/MedicationScheduleMealsAndFrequencyStep.vue';
import MedicationScheduleTimesPerDayStep from '@/Components/Patient/Medications/steps/MedicationScheduleTimesPerDayStep.vue';
import { mobileShellWizardStepPanelClass } from '@/lib/shell/mobileShellDialogLayout';

defineProps<{
    currentStep: MedicationFormWizardStep;
    form: MedicationCreateFormWithErrors;
    idPrefix: string;
    showStockFields?: boolean;
    goToWizardStepFromSummary?: (
        step: MedicationFormWizardStep,
        focusElementIdSuffix?: string,
    ) => void;
}>();

const noopGoToWizardStep: (
    step: MedicationFormWizardStep,
    focusElementIdSuffix?: string,
) => void = () => {
    // Summary edit navigation is only wired in dialog flows.
};
</script>

<template>
    <div :class="mobileShellWizardStepPanelClass">
        <MedicationScheduleMealsAndFrequencyStep
            v-if="currentStep === 2"
            :form="form"
            :id-prefix="idPrefix"
        />
        <MedicationCreateSummaryStep
            v-else-if="currentStep === 7"
            :form="form"
            :id-prefix="idPrefix"
            :go-to-wizard-step="goToWizardStepFromSummary ?? noopGoToWizardStep"
        />
        <MobileShellWizardCard v-else>
            <MedicationDetailsStep
                v-if="currentStep === 1"
                :form="form"
                :id-prefix="idPrefix"
            />
            <MedicationScheduleTimesPerDayStep
                v-else-if="currentStep === 3"
                :form="form"
                :id-prefix="idPrefix"
            />
            <MedicationScheduleDoseTimesStep
                v-else-if="currentStep === 4"
                :form="form"
                :id-prefix="idPrefix"
            />
            <MedicationScheduleDurationStep
                v-else-if="currentStep === 5"
                :form="form"
                :id-prefix="idPrefix"
            />
            <MedicationNoteStep
                v-else-if="currentStep === 6"
                :form="form"
                :id-prefix="idPrefix"
                :show-stock-fields="showStockFields ?? true"
            />
        </MobileShellWizardCard>
    </div>
</template>
