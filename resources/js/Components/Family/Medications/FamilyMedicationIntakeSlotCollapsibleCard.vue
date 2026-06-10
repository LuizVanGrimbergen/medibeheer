<script setup lang="ts">
import MedicationIntakeSlotStatusCard from '@/Components/shared/medications/MedicationIntakeSlotStatusCard.vue';
import type { FamilyMedicationIntakeCalendarSlot } from '@/lib/family/medications/familyMedicationIntakeCalendarSlot';

defineProps<{
    intakeSlot: FamilyMedicationIntakeCalendarSlot;
}>();
</script>

<template>
    <MedicationIntakeSlotStatusCard :intake-slot="intakeSlot" />
</template>
