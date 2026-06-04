<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import PrescriptionFormDialogFooter from '@/Components/Patient/Prescriptions/form/PrescriptionFormDialogFooter.vue';
import PrescriptionDetailsStep from '@/Components/Patient/Prescriptions/steps/PrescriptionDetailsStep.vue';
import PrescriptionExpiryDatesStep from '@/Components/Patient/Prescriptions/steps/PrescriptionExpiryDatesStep.vue';
import PatientShellWizardScrollBody from '@/Components/Patient/form/PatientShellWizardScrollBody.vue';
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
import type { PatientPrescriptionForm } from '@/lib/patient/prescriptions/patientPrescriptionFormTypes';
import type { PatientPrescriptionMedicationChoice } from '@/lib/patient/prescriptions/patientPrescriptionsScreenProps';
import type { PrescriptionFormWizardStep } from '@/lib/patient/prescriptions/prescriptionFormWizardTypes';
import { computed, ref } from 'vue';

const open = defineModel<boolean>('open', { required: true });
const selectedMedicationId = defineModel<number | null>(
    'selectedMedicationId',
    {
        required: true,
    },
);

const props = defineProps<{
    form: PatientPrescriptionForm;
    formId: string;
    idPrefix: string;
    dialogContentClass: string;
    medicationChoices: PatientPrescriptionMedicationChoice[];
    currentStep: PrescriptionFormWizardStep;
    progressLabel: string;
    quantityClientError: string;
    medicationClientError: string;
    expiryDatesClientError: string;
}>();

const emit = defineEmits<{
    submit: [];
    cancel: [];
    back: [];
}>();

const { t } = useI18n();

const isOpen = open;
const progressLabelRef = ref<HTMLElement | null>(null);

const currentStepIndex = computed(() => props.currentStep - 1);

const { wizardStepPanelRef } = usePatientFormWizardStepMotion(
    currentStepIndex,
    isOpen,
    { progressLabelRef },
);
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent
            :class="props.dialogContentClass"
            :overlay-class="patientShellDialogOverlayAboveAppChromeClass('md')"
        >
            <DialogHeader :class="patientShellPageHeaderClass">
                <DialogTitle :class="patientShellPageTitleClass">
                    {{ t('patient.prescriptions.dialogTitle') }}
                </DialogTitle>
                <DialogDescription
                    ref="progressLabelRef"
                    :class="patientShellPageDescriptionClass"
                    aria-live="polite"
                >
                    {{ progressLabel }}
                </DialogDescription>
            </DialogHeader>

            <form
                :id="props.formId"
                :class="patientShellWizardFormClass"
                novalidate
                @submit.prevent="emit('submit')"
            >
                <PatientShellWizardScrollBody
                    :active="open"
                    :step-key="props.currentStep"
                >
                    <div
                        ref="wizardStepPanelRef"
                        :class="patientShellWizardStepPanelClass"
                    >
                        <Card :class="patientShellWizardCardClass">
                            <CardContent class="p-0">
                                <div :class="patientShellWizardCardInnerClass">
                                    <PrescriptionDetailsStep
                                        v-if="currentStep === 1"
                                        :id-prefix="props.idPrefix"
                                        v-model:selected-medication-id="
                                            selectedMedicationId
                                        "
                                        :form="props.form"
                                        :medication-choices="
                                            props.medicationChoices
                                        "
                                        :quantity-client-error="
                                            props.quantityClientError
                                        "
                                        :medication-client-error="
                                            props.medicationClientError
                                        "
                                    />

                                    <PrescriptionExpiryDatesStep
                                        v-else
                                        :id-prefix="props.idPrefix"
                                        :form="props.form"
                                        :expiry-dates-client-error="
                                            props.expiryDatesClientError
                                        "
                                    />
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <template #footer>
                        <PrescriptionFormDialogFooter
                            :current-step="currentStep"
                            :processing="props.form.processing"
                            @cancel="emit('cancel')"
                            @back="emit('back')"
                        />
                    </template>
                </PatientShellWizardScrollBody>
            </form>
        </DialogContent>
    </Dialog>
</template>
