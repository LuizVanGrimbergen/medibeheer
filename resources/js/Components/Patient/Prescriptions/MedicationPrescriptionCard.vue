<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationListCardLead from '@/Components/Medications/MedicationListCardLead.vue';
import MedicationUrgencyProgressSection from '@/Components/Medications/MedicationUrgencyProgressSection.vue';
import PatientListCardActionsToolbar from '@/Components/Patient/PatientListCardActionsToolbar.vue';
import PatientListCardDetailsToggle from '@/Components/Patient/PatientListCardDetailsToggle.vue';
import MedicationPrescriptionListItemSection from '@/Components/Patient/Prescriptions/MedicationPrescriptionListItem.vue';
import PrescriptionLastAppointmentTag from '@/Components/Patient/Prescriptions/PrescriptionLastAppointmentTag.vue';
import PrescriptionPickupControl from '@/Components/Patient/Prescriptions/PrescriptionPickupControl.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Collapsible, CollapsibleContent } from '@/Components/ui/collapsible';
import { usePatientPrescriptionCompleteActions } from '@/composables/patient/usePatientPrescriptionCompleteActions';
import type { MedicationUrgencyTone } from '@/lib/patient/medications/urgency/medicationUrgencyTone';
import { medicationUrgencyToneClasses } from '@/lib/patient/medications/urgency/medicationUrgencyToneClasses';
import {
    prescriptionShowsExpandedPickupControl,
    prescriptionShowsPrimaryPickupAction,
} from '@/lib/patient/prescriptions/prescriptionCollapsedPickupVisibility';
import { prescriptionExpiryStatusLine } from '@/lib/patient/prescriptions/prescriptionExpiryStatusLine';
import { prescriptionExpiryUrgencyContext } from '@/lib/patient/prescriptions/prescriptionExpiryUrgency';
import { patientPageCardHeaderWithActionsClass } from '@/lib/patient/patientPageTypography';
import type {
    MedicationPrescriptionListItem,
    MedicationPrescriptionPickupStatusValue,
    MedicationTypeValue,
} from '@/lib/types';

const props = withDefaults(
    defineProps<{
        prescription: MedicationPrescriptionListItem;
        onPickedUp?: (isLastInBatch: boolean) => void;
        showActions?: boolean;
    }>(),
    {
        showActions: true,
    },
);

const emit = defineEmits<{
    edit: [];
    delete: [];
}>();

const { t } = useI18n();

const { isPrescriptionUpdateInFlight, updatePrescriptionPickupStatus } =
    usePatientPrescriptionCompleteActions();

const isOpen = ref(false);

const urgencyContext = computed(() =>
    prescriptionExpiryUrgencyContext(
        props.prescription.prescription_expiry_date,
    ),
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

const showPrimaryPickupAction = prescriptionShowsPrimaryPickupAction();

const showExpandedPickupControl = prescriptionShowsExpandedPickupControl();

const isPickupUpdateDisabled = computed(() =>
    isPrescriptionUpdateInFlight(props.prescription.id),
);

function onPickupStatusUpdate(
    pickupStatus: MedicationPrescriptionPickupStatusValue,
): void {
    const pickedUpHandler =
        pickupStatus === 'picked_up' && props.onPickedUp
            ? () => props.onPickedUp?.(props.prescription.is_last_in_batch)
            : null;

    updatePrescriptionPickupStatus(
        props.prescription.id,
        pickupStatus,
        pickedUpHandler,
    );
}
</script>

<template>
    <Card
        class="bg-surface text-text w-full min-w-0 rounded-3xl border shadow-md shadow-black/[0.04]"
        :class="prescriptionVisualToneClasses.border"
    >
        <CardContent class="relative p-6 sm:p-7">
            <Collapsible v-model:open="isOpen" class="flex flex-col gap-5">
                <PatientListCardActionsToolbar
                    v-if="props.showActions"
                    :ariaLabel="t('patient.prescriptions.cardActionsAriaLabel')"
                    :showEdit="true"
                    :showDelete="true"
                    :editAriaLabel="t('patient.prescriptions.actions.edit')"
                    :deleteAriaLabel="t('patient.prescriptions.actions.delete')"
                    @edit="emit('edit')"
                    @delete="emit('delete')"
                />

                <div
                    class="space-y-3.5"
                    :class="
                        props.showActions
                            ? patientPageCardHeaderWithActionsClass
                            : null
                    "
                >
                    <MedicationListCardLead
                        :name="prescription.medication.name"
                        :type-medication="
                            prescription.medication
                                .type_medication as MedicationTypeValue
                        "
                        :tone="prescriptionUrgencyTone"
                        :show-type-label="false"
                    >
                        <template
                            v-if="prescription.is_last_in_batch"
                            #title-before
                        >
                            <PrescriptionLastAppointmentTag />
                        </template>
                    </MedicationListCardLead>

                    <template v-if="!isOpen">
                        <MedicationUrgencyProgressSection
                            v-if="urgencyContext !== null"
                            :tone="urgencyContext.tone"
                            :progress-percent="urgencyContext.progress_percent"
                            :status-line="expiryStatusLine"
                            :progress-aria-label="expiryProgressAriaLabel"
                            :critical-alert-label="
                                t(
                                    'patient.prescriptions.expiryCriticalAlertLabel',
                                )
                            "
                            :warning-alert-label="
                                t('patient.prescriptions.expiryWarningAria')
                            "
                            :show-progress-bar="false"
                        />

                        <p
                            v-else
                            class="text-text-heading text-base leading-relaxed font-semibold sm:text-lg"
                        >
                            {{ expiryStatusLine }}
                        </p>
                    </template>
                </div>

                <PrescriptionPickupControl
                    v-if="showPrimaryPickupAction"
                    :pickup-status="prescription.pickup_status"
                    :disabled="isPickupUpdateDisabled"
                    @update:pickup-status="onPickupStatusUpdate"
                />

                <CollapsibleContent>
                    <MedicationPrescriptionListItemSection
                        :prescription="prescription"
                        :show-pickup-control="showExpandedPickupControl"
                        :is-pickup-update-disabled="isPickupUpdateDisabled"
                        class="border-border/70 border-t pt-5"
                        @update:pickup-status="onPickupStatusUpdate"
                    />
                </CollapsibleContent>

                <PatientListCardDetailsToggle
                    :mode="isOpen ? 'collapse' : 'expand'"
                    wrapper-class="mt-0 border-t-0 pt-0"
                    :label="
                        t(
                            isOpen
                                ? 'patient.medications.cardCollapseHint'
                                : 'patient.medications.cardExpandHint',
                        )
                    "
                    :ariaLabel="
                        t(
                            isOpen
                                ? 'patient.medications.hideDetails'
                                : 'patient.medications.showDetails',
                        )
                    "
                />
            </Collapsible>
        </CardContent>
    </Card>
</template>
