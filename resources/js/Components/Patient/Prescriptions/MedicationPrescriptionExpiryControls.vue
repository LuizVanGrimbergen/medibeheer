<script setup lang="ts">
import MedicationPrescriptionExpiryDatePanel from '@/Components/Patient/Prescriptions/MedicationPrescriptionExpiryDatePanel.vue';

withDefaults(
    defineProps<{
        prescriptionExpiryDate: string | null;
        prescriptionNumber?: number;
        showExpiryDatePanel?: boolean;
    }>(),
    {
        showExpiryDatePanel: true,
    },
);
</script>

<template>
    <MedicationPrescriptionExpiryDatePanel
        v-if="showExpiryDatePanel"
        :prescription-expiry-date="prescriptionExpiryDate"
        :prescription-number="prescriptionNumber"
    />
</template>
