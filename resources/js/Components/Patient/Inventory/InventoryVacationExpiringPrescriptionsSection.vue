<script setup lang="ts">
import MedicationUrgentPrescriptionStatusCard from '@/Components/shared/medications/MedicationUrgentPrescriptionStatusCard.vue';
import type { PatientInventoryVacationExpiringPrescription } from '@/lib/patient/inventory/patientInventoryVacationExpiringPrescription';

defineProps<{
    prescriptions: PatientInventoryVacationExpiringPrescription[];
}>();
</script>

<template>
    <div v-if="prescriptions.length > 0" class="space-y-3">
        <MedicationUrgentPrescriptionStatusCard
            v-for="prescription in prescriptions"
            :key="prescription.id"
            :prescription="prescription"
        />
    </div>
</template>
