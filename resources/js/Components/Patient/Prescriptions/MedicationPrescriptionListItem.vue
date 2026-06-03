<script setup lang="ts">
import { computed } from 'vue';
import MedicationPrescriptionExpiryControls from '@/Components/Patient/Prescriptions/MedicationPrescriptionExpiryControls.vue';
import PrescriptionPickupControl from '@/Components/Patient/Prescriptions/PrescriptionPickupControl.vue';
import { usePatientPrescriptionCompleteActions } from '@/composables/patient/usePatientPrescriptionCompleteActions';
import type {
    MedicationPrescriptionListItem,
    MedicationPrescriptionPickupStatusValue,
} from '@/lib/types';

const props = defineProps<{
    prescription: Pick<
        MedicationPrescriptionListItem,
        'id' | 'prescription_expiry_date' | 'pickup_status'
    >;
}>();

const { isPrescriptionUpdateInFlight, updatePrescriptionPickupStatus } =
    usePatientPrescriptionCompleteActions();

const isPickupUpdateDisabled = computed(() =>
    isPrescriptionUpdateInFlight(props.prescription.id),
);

function onPickupStatusUpdate(
    pickupStatus: MedicationPrescriptionPickupStatusValue,
): void {
    updatePrescriptionPickupStatus(props.prescription.id, pickupStatus);
}
</script>

<template>
    <section class="space-y-3.5">
        <PrescriptionPickupControl
            :pickup-status="prescription.pickup_status"
            :disabled="isPickupUpdateDisabled"
            @update:pickup-status="onPickupStatusUpdate"
        />

        <MedicationPrescriptionExpiryControls
            :prescription-expiry-date="prescription.prescription_expiry_date"
        />
    </section>
</template>
