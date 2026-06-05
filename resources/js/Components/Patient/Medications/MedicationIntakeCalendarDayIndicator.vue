<script setup lang="ts">
import { computed } from 'vue';
import { MedicationIntakeDayIcon } from '@/Components/ui/medication-intake-day-icon';
import { todayIsoDateKey } from '@/lib/history/formatHistoryCalendarDate';
import type { HistoryMonthCalendarCell } from '@/lib/history/historyMonthCalendarTypes';
import { isMedicationIntakeDayIconStatus } from '@/lib/patient/medications/history/medicationIntakeDayPresentation';
import type {
    MedicationIntakeCalendarDay,
    MedicationIntakeDayStatusValue,
} from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';

const props = defineProps<{
    cell: HistoryMonthCalendarCell;
    daysByDate: Record<string, MedicationIntakeCalendarDay>;
}>();

const calendarDay = computed((): MedicationIntakeCalendarDay | undefined => {
    if (props.cell.dateKey === null) {
        return undefined;
    }

    return props.daysByDate[props.cell.dateKey];
});

const indicatorStatus = computed((): MedicationIntakeDayStatusValue | null => {
    const day = calendarDay.value;

    if (day === undefined) {
        return 'no_schedule';
    }

    if (day.status === 'none_taken' && day.date > todayIsoDateKey()) {
        return 'no_schedule';
    }

    return day.status;
});

const iconStatus = computed(() => {
    const status = indicatorStatus.value;

    if (status === null || !isMedicationIntakeDayIconStatus(status)) {
        return null;
    }

    return status;
});
</script>

<template>
    <MedicationIntakeDayIcon
        v-if="iconStatus !== null"
        :status="iconStatus"
        size="calendar-day"
    />
    <span
        v-else
        class="bg-border size-1.5 rounded-full sm:size-2"
        aria-hidden="true"
    />
</template>
