<script setup lang="ts">
import MedicationFormDialogFooter from '@/Components/Patient/Medications/form/MedicationFormDialogFooter.vue';
import type { MedicationCreateFormWithErrors } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { useMedicationFormWizard } from '@/Components/Patient/Medications/form/useMedicationFormWizard';
import PatientShellWizardScrollBody from '@/Components/Patient/form/PatientShellWizardScrollBody.vue';
import MedicationCreateSummaryStep from '@/Components/Patient/Medications/steps/MedicationCreateSummaryStep.vue';
import MedicationDetailsStep from '@/Components/Patient/Medications/steps/MedicationDetailsStep.vue';
import MedicationNoteStep from '@/Components/Patient/Medications/steps/MedicationNoteStep.vue';
import MedicationScheduleDoseTimesStep from '@/Components/Patient/Medications/steps/MedicationScheduleDoseTimesStep.vue';
import MedicationScheduleDurationStep from '@/Components/Patient/Medications/steps/MedicationScheduleDurationStep.vue';
import MedicationScheduleMealsAndFrequencyStep from '@/Components/Patient/Medications/steps/MedicationScheduleMealsAndFrequencyStep.vue';
import MedicationScheduleTimesPerDayStep from '@/Components/Patient/Medications/steps/MedicationScheduleTimesPerDayStep.vue';
import { Card, CardContent } from '@/Components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { usePatientFormWizardStepMotion } from '@/composables/motion/usePatientFormWizardStepMotion';
import {
    patientShellDialogOverlayAboveAppChromeClass,
    patientShellPageDescriptionClass,
    patientShellPageHeaderClass,
    patientShellPageTitleClass,
    patientShellWizardCardClass,
    patientShellWizardCardInnerClass,
    patientShellWizardFormClass,
    patientShellWizardStepPanelClass,
} from '@/lib/patient/patientShellDialogLayout';
import { ref, toRef } from 'vue';

const props = defineProps<{
    open: boolean;
    title: string;
    formId: string;
    idPrefix: string;
    form: MedicationCreateFormWithErrors;
    dialogContentClass: string;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    submit: [];
    cancel: [];
}>();

const {
    currentStep,
    medicationProgressLabel,
    handleSubmit,
    handleMedicationFormFooterBack,
    goToMedicationWizardStepFromSummary,
} = useMedicationFormWizard({
    open: () => props.open,
    form: () => props.form,
    idPrefix: () => props.idPrefix,
    onSubmit: () => {
        emit('submit');
    },
});

const isOpen = toRef(() => props.open);
const progressLabelRef = ref<HTMLElement | null>(null);

const { wizardStepPanelRef } = usePatientFormWizardStepMotion(
    currentStep,
    isOpen,
    { progressLabelRef },
);
</script>

<template>
    <Dialog :open="props.open" @update:open="emit('update:open', $event)">
        <DialogContent
            :class="props.dialogContentClass"
            :overlay-class="patientShellDialogOverlayAboveAppChromeClass('md')"
        >
            <DialogHeader :class="patientShellPageHeaderClass">
                <DialogTitle :class="patientShellPageTitleClass">
                    {{ props.title }}
                </DialogTitle>
                <DialogDescription
                    ref="progressLabelRef"
                    :class="patientShellPageDescriptionClass"
                    aria-live="polite"
                >
                    {{ medicationProgressLabel }}
                </DialogDescription>
            </DialogHeader>

            <form
                :id="props.formId"
                :class="patientShellWizardFormClass"
                novalidate
                @submit.prevent="handleSubmit"
            >
                <PatientShellWizardScrollBody
                    :active="props.open"
                    :step-key="currentStep"
                >
                    <div
                        ref="wizardStepPanelRef"
                        :class="patientShellWizardStepPanelClass"
                    >
                        <MedicationScheduleMealsAndFrequencyStep
                            v-if="currentStep === 2"
                            :form="props.form"
                            :id-prefix="props.idPrefix"
                        />
                        <Card v-else :class="patientShellWizardCardClass">
                            <CardContent class="p-0">
                                <div :class="patientShellWizardCardInnerClass">
                                    <MedicationDetailsStep
                                        v-if="currentStep === 1"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                    />
                                    <MedicationScheduleTimesPerDayStep
                                        v-else-if="currentStep === 3"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                    />
                                    <MedicationScheduleDoseTimesStep
                                        v-else-if="currentStep === 4"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                    />
                                    <MedicationScheduleDurationStep
                                        v-else-if="currentStep === 5"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                    />
                                    <MedicationNoteStep
                                        v-else-if="currentStep === 6"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                        show-stock-fields
                                    />
                                    <MedicationCreateSummaryStep
                                        v-else-if="currentStep === 7"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                        :go-to-wizard-step="
                                            goToMedicationWizardStepFromSummary
                                        "
                                    />
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <template #footer>
                        <MedicationFormDialogFooter
                            :current-step="currentStep"
                            :processing="props.form.processing"
                            @cancel="emit('cancel')"
                            @back="handleMedicationFormFooterBack"
                        />
                    </template>
                </PatientShellWizardScrollBody>
            </form>
        </DialogContent>
    </Dialog>
</template>
