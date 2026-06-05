<script setup lang="ts">
import MedicationPrescriptionExpiryControls from '@/Components/Patient/Prescriptions/MedicationPrescriptionExpiryControls.vue';
import PrescriptionPickupControl from '@/Components/Patient/Prescriptions/PrescriptionPickupControl.vue';
import type {
    MedicationPrescriptionListItem,
    MedicationPrescriptionPickupStatusValue,
} from '@/lib/types';

withDefaults(
    defineProps<{
        prescription: Pick<
            MedicationPrescriptionListItem,
            'id' | 'prescription_expiry_date' | 'pickup_status'
        >;
        showPickupControl?: boolean;
        isPickupUpdateDisabled?: boolean;
    }>(),
    {
        showPickupControl: true,
        isPickupUpdateDisabled: false,
    },
);

const emit = defineEmits<{
    'update:pickupStatus': [value: MedicationPrescriptionPickupStatusValue];
}>();
</script>

<template>
    <section class="space-y-3.5">
        <PrescriptionPickupControl
            v-if="showPickupControl"
            :pickup-status="prescription.pickup_status"
            :disabled="isPickupUpdateDisabled"
            @update:pickup-status="emit('update:pickupStatus', $event)"
        />

        <MedicationPrescriptionExpiryControls
            :prescription-expiry-date="prescription.prescription_expiry_date"
        />
    </section>
</template>
