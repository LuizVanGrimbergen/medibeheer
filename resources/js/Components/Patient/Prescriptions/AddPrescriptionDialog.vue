<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import PrescriptionFormDialogFooter from '@/Components/Patient/Prescriptions/form/PrescriptionFormDialogFooter.vue';
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
import type { PatientPrescriptionForm } from '@/lib/patient/prescriptions/patientPrescriptionFormTypes';
import type { PatientPrescriptionMedicationChoice } from '@/lib/patient/prescriptions/patientPrescriptionsScreenProps';
import type { PrescriptionFormWizardStep } from '@/lib/patient/prescriptions/prescriptionFormWizardTypes';
import { patientShellDialogOverlayAboveAppChromeClass } from '@/lib/patient/patientShellDialogLayout';

const open = defineModel<boolean>('open', { required: true });
const selectedMedicationId = defineModel<number | null>('selectedMedicationId', {
    required: true,
});

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
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent
            :class="props.dialogContentClass"
            :overlay-class="patientShellDialogOverlayAboveAppChromeClass('md')"
        >
            <DialogHeader
                class="shrink-0 space-y-1.5 pt-[env(safe-area-inset-top,0)] text-left sm:space-y-1 sm:pt-0 md:space-y-1"
            >
                <DialogTitle
                    class="text-xl font-bold leading-tight text-text-heading md:text-2xl"
                >
                    {{ t('patient.prescriptions.dialogTitle') }}
                </DialogTitle>
                <DialogDescription
                    class="block text-sm font-medium leading-snug text-text-heading md:text-base md:leading-relaxed"
                    aria-live="polite"
                >
                    {{ progressLabel }}
                </DialogDescription>
            </DialogHeader>

            <form
                :id="props.formId"
                class="flex min-h-0 flex-1 flex-col"
                novalidate
                @submit.prevent="emit('submit')"
            >
                <div
                    class="min-h-0 flex-1 overflow-y-auto overscroll-y-contain [-webkit-overflow-scrolling:touch] touch-pan-y"
                >
                    <div class="space-y-3 md:space-y-3">
                        <Card
                            class="rounded-2xl border border-border/80 bg-surface text-text shadow-md shadow-black/[0.04] md:rounded-3xl"
                        >
                            <CardContent class="p-0">
                                <div
                                    class="space-y-5 rounded-2xl bg-surface px-4 py-4 md:space-y-5 md:rounded-3xl md:px-5 md:py-5 lg:space-y-6 lg:px-7 lg:py-7"
                                >
                                    <PrescriptionDetailsStep
                                        v-if="currentStep === 1"
                                        :id-prefix="props.idPrefix"
                                        v-model:selected-medication-id="selectedMedicationId"
                                        :form="props.form"
                                        :medication-choices="props.medicationChoices"
                                        :quantity-client-error="props.quantityClientError"
                                        :medication-client-error="props.medicationClientError"
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
                </div>

                <div
                    class="pointer-events-auto relative z-10 shrink-0 pt-2 pb-[max(0.75rem,env(safe-area-inset-bottom,0px))]"
                >
                    <Card
                        class="rounded-2xl border border-border/80 bg-transparent text-text shadow-sm shadow-black/[0.03] md:rounded-3xl"
                    >
                        <CardContent
                            class="px-4 py-3 md:px-5 md:py-3.5 lg:px-7 lg:py-4"
                        >
                            <PrescriptionFormDialogFooter
                                :current-step="currentStep"
                                :processing="props.form.processing"
                                @cancel="emit('cancel')"
                                @back="emit('back')"
                            />
                        </CardContent>
                    </Card>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
