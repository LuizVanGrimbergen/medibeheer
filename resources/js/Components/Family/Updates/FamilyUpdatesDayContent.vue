<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyWellbeingCheckinCard from '@/Components/Family/Wellbeing/FamilyWellbeingCheckinCard.vue';
import MedicationIntakeHistorySlotCard from '@/Components/Patient/Medications/MedicationIntakeHistorySlotCard.vue';
import type { MedicationIntakeHistorySlot } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';
import type { DailyCheckin } from '@/lib/types';

const props = defineProps<{
    checkins: DailyCheckin[];
    medicationIntakes: MedicationIntakeHistorySlot[];
}>();

const { t } = useI18n();

const hasUpdates = computed(
    (): boolean =>
        props.checkins.length > 0 || props.medicationIntakes.length > 0,
);
</script>

<template>
    <p v-if="!hasUpdates" class="text-text-muted text-sm leading-relaxed">
        {{ t('family.updates.emptyToday') }}
    </p>

    <ul v-else class="space-y-4 md:space-y-3">
        <li v-for="checkin in props.checkins" :key="checkin.id">
            <FamilyWellbeingCheckinCard :checkin="checkin" />
        </li>

        <li
            v-for="intake in props.medicationIntakes"
            :key="`${intake.medication_schedule_id}-${intake.dose_time}`"
        >
            <MedicationIntakeHistorySlotCard
                :intake-slot="intake"
                density="compact"
            />
        </li>
    </ul>
</template>
