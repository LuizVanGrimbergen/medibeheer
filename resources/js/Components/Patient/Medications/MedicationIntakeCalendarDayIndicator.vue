<script setup lang="ts">
import type { LucideIcon } from 'lucide-vue-next';
import { AlertCircle, Check, Minus } from 'lucide-vue-next';
import { computed } from 'vue';
import { todayIsoDateKey } from '@/lib/history/formatHistoryCalendarDate';
import type { HistoryMonthCalendarCell } from '@/lib/history/historyMonthCalendarTypes';
import type {
    MedicationIntakeCalendarDay,
    MedicationIntakeDayStatusValue,
} from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';
import { cn } from '@/lib/utils';

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

const glyph = computed((): LucideIcon | null => {
    if (indicatorStatus.value === 'complete') {
        return Check;
    }

    if (indicatorStatus.value === 'partial') {
        return Minus;
    }

    if (indicatorStatus.value === 'none_taken') {
        return AlertCircle;
    }

    return null;
});

const iconClass = computed((): string => {
    if (indicatorStatus.value === 'complete') {
        return 'text-success';
    }

    if (indicatorStatus.value === 'partial') {
        return 'text-warning';
    }

    if (indicatorStatus.value === 'none_taken') {
        return 'text-danger';
    }

    return 'text-border';
});
</script>

<template>
    <component
        v-if="glyph !== null"
        :is="glyph"
        :class="
            cn(
                'size-[1.05rem] shrink-0 stroke-[2.25] sm:size-5',
                iconClass,
            )
        "
        aria-hidden="true"
    />
    <span
        v-else
        class="size-1.5 rounded-full bg-border sm:size-2"
        aria-hidden="true"
    />
</template>
