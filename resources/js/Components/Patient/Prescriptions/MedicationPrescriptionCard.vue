<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationListCardLead from '@/Components/Medications/MedicationListCardLead.vue';
import MedicationUrgencyProgressSection from '@/Components/Medications/MedicationUrgencyProgressSection.vue';
import PatientListCardDetailsToggle from '@/Components/Patient/PatientListCardDetailsToggle.vue';
import MedicationPrescriptionListItemSection from '@/Components/Patient/Prescriptions/MedicationPrescriptionListItem.vue';
import PrescriptionLastAppointmentTag from '@/Components/Patient/Prescriptions/PrescriptionLastAppointmentTag.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Collapsible, CollapsibleContent } from '@/Components/ui/collapsible';
import type { MedicationUrgencyTone } from '@/lib/patient/medications/urgency/medicationUrgencyTone';
import { medicationUrgencyToneClasses } from '@/lib/patient/medications/urgency/medicationUrgencyToneClasses';
import { prescriptionExpiryStatusLine } from '@/lib/patient/prescriptions/prescriptionExpiryStatusLine';
import { prescriptionExpiryUrgencyContext } from '@/lib/patient/prescriptions/prescriptionExpiryUrgency';
import type { MedicationPrescriptionListItem, MedicationTypeValue } from '@/lib/types';

const props = defineProps<{
    prescription: MedicationPrescriptionListItem;
}>();

const { t } = useI18n();

const isOpen = ref(false);

const urgencyContext = computed(() =>
    prescriptionExpiryUrgencyContext(props.prescription.prescription_expiry_date),
);

const prescriptionUrgencyTone = computed((): MedicationUrgencyTone | null => {
    return urgencyContext.value?.tone ?? null;
});

const prescriptionVisualToneClasses = computed(() =>
    medicationUrgencyToneClasses(prescriptionUrgencyTone.value),
);

const expiryStatusLine = computed((): string => {
    const context = urgencyContext.value;

    if (context === null) {
        return t('patient.prescriptions.prescriptionExpiryMissing');
    }

    return prescriptionExpiryStatusLine(t, context.days_remaining);
});

const expiryProgressAriaLabel = computed((): string => {
    const context = urgencyContext.value;

    if (context === null) {
        return t('patient.prescriptions.expiryProgressUnknownAria');
    }

    return t('patient.prescriptions.expiryProgressAria', {
        days: String(context.days_remaining),
    });
});

</script>

<template>
    <Card
        class="min-w-0 w-full rounded-3xl border bg-surface text-text shadow-md shadow-black/[0.04]"
        :class="prescriptionVisualToneClasses.border"
    >
        <CardContent class="relative p-6 sm:p-7">
            <div
                v-if="prescription.is_last_in_batch"
                class="mb-4 flex w-full min-w-0 sm:absolute sm:right-6 sm:top-6 sm:z-10 sm:mb-0 sm:w-auto sm:justify-end"
            >
                <PrescriptionLastAppointmentTag />
            </div>

            <Collapsible v-model:open="isOpen">
                <MedicationListCardLead
                    :name="prescription.medication.name"
                    :type-medication="
                        prescription.medication.type_medication as MedicationTypeValue
                    "
                    :tone="prescriptionUrgencyTone"
                    :show-type-label="false"
                    :class="prescription.is_last_in_batch ? 'sm:pr-44' : null"
                >
                    <template
                        v-if="!isOpen"
                        #subtitle
                    >
                        <MedicationUrgencyProgressSection
                            v-if="urgencyContext !== null"
                            :tone="urgencyContext.tone"
                            :progress-percent="urgencyContext.progress_percent"
                            :status-line="expiryStatusLine"
                            :progress-aria-label="expiryProgressAriaLabel"
                            :critical-alert-label="
                                t('patient.prescriptions.expiryCriticalAlertLabel')
                            "
                            :warning-alert-label="t('patient.prescriptions.expiryWarningAria')"
                            :show-progress-bar="false"
                        />

                        <p
                            v-else
                            class="text-base font-semibold leading-relaxed text-text-heading sm:text-lg"
                        >
                            {{ expiryStatusLine }}
                        </p>
                    </template>
                </MedicationListCardLead>

                <PatientListCardDetailsToggle
                    v-if="!isOpen"
                    mode="expand"
                    wrapper-class="mt-5 border-t-0 pt-0"
                    :label="t('patient.medications.cardExpandHint')"
                    :ariaLabel="t('patient.medications.showDetails')"
                />

                <CollapsibleContent>
                    <MedicationPrescriptionListItemSection
                        :prescription="prescription"
                        class="mt-5 border-t border-border/70 pt-5"
                    />

                    <PatientListCardDetailsToggle
                        mode="collapse"
                        wrapper-class="mt-5 border-t border-border/50 pt-4"
                        :label="t('patient.medications.cardCollapseHint')"
                        :ariaLabel="t('patient.medications.hideDetails')"
                    />
                </CollapsibleContent>
            </Collapsible>
        </CardContent>
    </Card>
</template>
