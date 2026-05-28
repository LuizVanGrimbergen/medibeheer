<script setup lang="ts">
import { computed, watch } from 'vue';
import MedicationFormDialogFooter from '@/Components/Patient/Medications/form/MedicationFormDialogFooter.vue';
import type {
    MedicationCreateFormWithErrors,
    MedicationFormWizardStep,
} from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { useMedicationFormWizard } from '@/Components/Patient/Medications/form/useMedicationFormWizard';
import MedicationCreateSummaryStep from '@/Components/Patient/Medications/steps/MedicationCreateSummaryStep.vue';
import MedicationDetailsStep from '@/Components/Patient/Medications/steps/MedicationDetailsStep.vue';
import MedicationNoteStep from '@/Components/Patient/Medications/steps/MedicationNoteStep.vue';
import MedicationScheduleDoseTimesStep from '@/Components/Patient/Medications/steps/MedicationScheduleDoseTimesStep.vue';
import MedicationScheduleDurationStep from '@/Components/Patient/Medications/steps/MedicationScheduleDurationStep.vue';
import MedicationScheduleMealsAndFrequencyStep from '@/Components/Patient/Medications/steps/MedicationScheduleMealsAndFrequencyStep.vue';
import MedicationScheduleTimesPerDayStep from '@/Components/Patient/Medications/steps/MedicationScheduleTimesPerDayStep.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

const props = defineProps<{
    title: string;
    formId: string;
    idPrefix: string;
    form: MedicationCreateFormWithErrors;
    startAtSummary?: boolean;
}>();

const emit = defineEmits<{
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
    open: () => true,
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
</script>

<template>
    <Card class="rounded-2xl border-border bg-surface shadow-sm">
        <CardHeader class="border-b border-border px-5 py-4 sm:px-6">
            <CardTitle class="text-xl font-bold text-text-heading">
                {{ title }}
            </CardTitle>
        </CardHeader>
        <CardContent class="px-5 py-6 sm:px-6">
            <form
                :id="formId"
                class="flex min-w-0 flex-col gap-6"
                novalidate
                @submit.prevent="handleSubmit"
            >
                <p
                    v-if="showWizardProgress"
                    class="text-sm text-text-muted"
                >
                    {{ medicationProgressLabel }}
                </p>

                <MedicationDetailsStep
                    v-if="currentStep === 1"
                    :form="form"
                    :id-prefix="idPrefix"
                />
                <MedicationScheduleMealsAndFrequencyStep
                    v-else-if="currentStep === 2"
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
                    show-stock-fields
                />
                <MedicationCreateSummaryStep
                    v-else-if="currentStep === 7"
                    :form="form"
                    :id-prefix="idPrefix"
                    :go-to-wizard-step="goToMedicationWizardStepFromSummary"
                />

                <MedicationFormDialogFooter
                    :current-step="currentStep"
                    :processing="form.processing"
                    @cancel="emit('cancel')"
                    @back="handleMedicationFormFooterBack"
                />
            </form>
        </CardContent>
    </Card>
</template>
