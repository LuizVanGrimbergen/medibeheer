<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationIntakeHistorySlotCard from '@/Components/Patient/Medications/MedicationIntakeHistorySlotCard.vue';
import type { MedicationSlotsByDate } from '@/lib/family/updates/groupMedicationSlotsByDate';

const props = defineProps<{
    group: MedicationSlotsByDate;
}>();

const { locale } = useI18n();

const formattedDate = computed((): string => {
    const [y, m, d] = props.group.date.split('-').map(Number);

    if (! y || ! m || ! d) {
        return props.group.date;
    }

    const date = new Date(y, m - 1, d);

    return new Intl.DateTimeFormat(locale.value === 'nl' ? 'nl-NL' : undefined, {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(date);
});
</script>

<template>
    <div class="space-y-3">
        <p class="text-xs font-medium uppercase tracking-wide text-text-muted">
            {{ formattedDate }}
        </p>
        <MedicationIntakeHistorySlotCard
            v-for="slot in props.group.slots"
            :key="`${slot.medication_schedule_id}-${slot.dose_time}-${slot.intake_date}`"
            :slot="slot"
        />
    </div>
</template>
