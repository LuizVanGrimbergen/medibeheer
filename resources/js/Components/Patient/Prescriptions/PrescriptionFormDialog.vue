<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import MobileShellWizardScrollBody from '@/Components/shell/MobileShellWizardScrollBody.vue';
import PrescriptionFormDialogFooter from '@/Components/Patient/Prescriptions/form/PrescriptionFormDialogFooter.vue';
import PrescriptionCreateSummaryStep from '@/Components/Patient/Prescriptions/steps/PrescriptionCreateSummaryStep.vue';
import PrescriptionDetailsStep from '@/Components/Patient/Prescriptions/steps/PrescriptionDetailsStep.vue';
import PrescriptionExpiryDatesStep from '@/Components/Patient/Prescriptions/steps/PrescriptionExpiryDatesStep.vue';
import { Card, CardContent } from '@/Components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { usePatientFormWizardStepMotion } from '@/composables/motion/usePatientFormWizardStepMotion';
import { useMobileShellDialogChromeSync } from '@/composables/patient/useMobileShellDialogChrome';
import {
    mobileShellDialogOverlayAboveAppChromeClass,
    mobileShellDialogDescriptionClass,
    mobileShellDialogHeaderClass,
    mobileShellDialogTitleClass,
    mobileShellWizardCardClass,
    mobileShellWizardCardInnerClass,
    mobileShellWizardFormClass,
    mobileShellWizardStepPanelClass,
} from '@/lib/shell/mobileShellDialogLayout';
import type { PatientPrescriptionForm } from '@/lib/patient/prescriptions/patientPrescriptionFormTypes';
import type { PatientPrescriptionMedicationChoice } from '@/lib/patient/prescriptions/patientPrescriptionsScreenProps';
import type { PrescriptionFormWizardStep } from '@/lib/patient/prescriptions/prescriptionFormWizardTypes';

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
    goToWizardStepFromSummary: (
        step: PrescriptionFormWizardStep,
        focusElementIdSuffix?: string,
    ) => void;
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

useMobileShellDialogChromeSync(open);
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent
            :class="props.dialogContentClass"
            :overlay-class="mobileShellDialogOverlayAboveAppChromeClass('md')"
        >
            <DialogHeader :class="mobileShellDialogHeaderClass">
                <DialogTitle :class="mobileShellDialogTitleClass">
                    {{ t('patient.prescriptions.dialogTitle') }}
                </DialogTitle>
                <DialogDescription
                    ref="progressLabelRef"
                    :class="mobileShellDialogDescriptionClass"
                    aria-live="polite"
                >
                    {{ progressLabel }}
                </DialogDescription>
            </DialogHeader>

            <form
                :id="props.formId"
                :class="mobileShellWizardFormClass"
                novalidate
                @submit.prevent="emit('submit')"
            >
                <MobileShellWizardScrollBody
                    :active="open"
                    :step-key="props.currentStep"
                >
                    <div
                        ref="wizardStepPanelRef"
                        :class="mobileShellWizardStepPanelClass"
                    >
                        <PrescriptionCreateSummaryStep
                            v-if="currentStep === 3"
                            :form="props.form"
                            :id-prefix="props.idPrefix"
                            :selected-medication-id="selectedMedicationId"
                            :medication-choices="props.medicationChoices"
                            :go-to-wizard-step="
                                props.goToWizardStepFromSummary
                            "
                        />

                        <Card
                            v-else
                            :class="mobileShellWizardCardClass"
                        >
                            <CardContent class="p-0">
                                <div :class="mobileShellWizardCardInnerClass">
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
                                        v-else-if="currentStep === 2"
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
                </MobileShellWizardScrollBody>
            </form>
        </DialogContent>
    </Dialog>
</template>
