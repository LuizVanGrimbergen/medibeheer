<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationIntakeHistorySlotCard from '@/Components/Patient/Medications/MedicationIntakeHistorySlotCard.vue';
import type { MedicationIntakeHistorySlot } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';

const props = defineProps<{
    date: string;
    intakes: MedicationIntakeHistorySlot[];
}>();

const { d } = useI18n();

const dateLabel = computed((): string =>
    d(props.date, {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
    }),
);
</script>

<template>
    <section class="space-y-3">
        <h3 class="text-sm font-semibold text-text-heading">
            {{ dateLabel }}
        </h3>

        <div class="flex flex-col gap-3">
            <MedicationIntakeHistorySlotCard
                v-for="intake in props.intakes"
                :key="`${intake.medication_schedule_id}-${intake.dose_time}`"
                :intake-slot="intake"
                density="compact"
            />
        </div>
    </section>
</template>
