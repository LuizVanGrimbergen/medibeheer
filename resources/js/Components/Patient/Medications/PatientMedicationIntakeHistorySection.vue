<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import HistorySelectedDaySection from '@/Components/History/HistorySelectedDaySection.vue';
import MedicationIntakeHistorySlotCard from '@/Components/Patient/Medications/MedicationIntakeHistorySlotCard.vue';
import MedicationIntakeMonthCalendar from '@/Components/Patient/Medications/MedicationIntakeMonthCalendar.vue';
import { useHistorySelectedDay } from '@/composables/useHistorySelectedDay';
import type {
    MedicationIntakeCalendarDay,
    MedicationIntakeHistorySlot,
} from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';
import { compareTodayMedicationIntakeSlots } from '@/lib/patient/medications/todayMedicationIntakeDayPeriod';

const props = defineProps<{
    calendarMonth: string;
    calendarDays: MedicationIntakeCalendarDay[];
    calendarSlots: MedicationIntakeHistorySlot[];
    navigateRouteName: string;
    navigateQueryKey?: string;
    selectedDayHeadingKey: string;
    selectedDayNoScheduleKey: string;
    selectedDayNoIntakesKey: string;
}>();

const { t } = useI18n();

const {
    selectedCalendarDate,
    selectedDaySectionRef,
    onSelectCalendarDate,
} = useHistorySelectedDay(() => props.calendarMonth);

const slotsByDate = computed((): Map<string, MedicationIntakeHistorySlot[]> => {
    const map = new Map<string, MedicationIntakeHistorySlot[]>();

    for (const slot of props.calendarSlots) {
        const existing = map.get(slot.intake_date) ?? [];

        existing.push(slot);
        map.set(slot.intake_date, existing);
    }

    for (const [date, slots] of map.entries()) {
        map.set(date, [...slots].sort(compareTodayMedicationIntakeSlots));
    }

    return map;
});

const selectedDaySlots = computed((): MedicationIntakeHistorySlot[] => {
    const date = selectedCalendarDate.value;

    if (date === null) {
        return [];
    }

    return slotsByDate.value.get(date) ?? [];
});

const selectedDayHasSchedule = computed((): boolean => {
    const date = selectedCalendarDate.value;

    if (date === null) {
        return false;
    }

    return props.calendarDays.some(
        (day) =>
            day.date === date &&
            day.status !== 'no_schedule',
    );
});
</script>

<template>
    <div class="flex min-w-0 flex-col gap-6">
        <MedicationIntakeMonthCalendar
            :calendar-month="props.calendarMonth"
            :calendar-days="props.calendarDays"
            :selected-date="selectedCalendarDate"
            :navigate-route-name="props.navigateRouteName"
            :navigate-query-key="props.navigateQueryKey"
            @select-date="onSelectCalendarDate"
        />

        <HistorySelectedDaySection
            ref="selectedDaySectionRef"
            :selected-date="selectedCalendarDate"
            :heading="t(props.selectedDayHeadingKey)"
        >
            <p
                v-if="!selectedDayHasSchedule"
                class="text-sm leading-relaxed text-text-muted"
            >
                {{ t(props.selectedDayNoScheduleKey) }}
            </p>

            <p
                v-else-if="selectedDaySlots.length === 0"
                class="text-sm leading-relaxed text-text-muted"
            >
                {{ t(props.selectedDayNoIntakesKey) }}
            </p>

            <div
                v-else
                class="flex flex-col gap-4"
            >
                <MedicationIntakeHistorySlotCard
                    v-for="slot in selectedDaySlots"
                    :key="`${slot.medication_schedule_id}-${slot.dose_time}`"
                    :slot="slot"
                />
            </div>
        </HistorySelectedDaySection>
    </div>
</template>
