<script setup lang="ts">
import { AlertCircle, Check, Minus } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import HistoryMonthCalendar from '@/Components/History/HistoryMonthCalendar.vue';
import MedicationIntakeCalendarDayIndicator from '@/Components/Patient/Medications/MedicationIntakeCalendarDayIndicator.vue';
import { formatHistoryCalendarLongDate, todayIsoDateKey } from '@/lib/history/formatHistoryCalendarDate';
import type { HistoryMonthCalendarCell } from '@/lib/history/historyMonthCalendarTypes';
import { indexMedicationIntakeCalendarDays } from '@/lib/patient/medications/history/indexMedicationIntakeCalendarDays';
import type { MedicationIntakeCalendarDay } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';

const props = withDefaults(
    defineProps<{
        calendarMonth: string;
        calendarDays: MedicationIntakeCalendarDay[];
        selectedDate?: string | null;
        navigateRouteName: string;
        navigateQueryKey?: string;
        density?: 'default' | 'compact';
        showMonthNavigation?: boolean;
        headerTitle?: string;
    }>(),
    {
        selectedDate: null,
        navigateQueryKey: 'calendar_month',
        density: 'default',
        showMonthNavigation: true,
        headerTitle: undefined,
    },
);

const emit = defineEmits<{
    selectDate: [dateKey: string];
}>();

const { t, locale } = useI18n();

const daysByDate = computed(() => indexMedicationIntakeCalendarDays(props.calendarDays));

function calendarDayForCell(
    cell: HistoryMonthCalendarCell,
): MedicationIntakeCalendarDay | undefined {
    if (cell.dateKey === null) {
        return undefined;
    }

    return daysByDate.value[cell.dateKey];
}

function dayAriaLabel(cell: HistoryMonthCalendarCell): string {
    if (cell.dateKey === null) {
        return '';
    }

    const longDate = formatHistoryCalendarLongDate(cell.dateKey, locale.value);
    const day = calendarDayForCell(cell);
    const todayKey = todayIsoDateKey();

    if (day === undefined || day.status === 'no_schedule') {
        return t('patient.medications.history.calendar.dayNoSchedule', { date: longDate });
    }

    if (day.status === 'none_taken' && day.date > todayKey) {
        return t('patient.medications.history.calendar.dayNoIntake', { date: longDate });
    }

    return t('patient.medications.history.calendar.dayWithStatus', {
        date: longDate,
        status: t(`patient.medications.history.status.${day.status}`),
    });
}
</script>

<template>
    <HistoryMonthCalendar
        :calendar-month="props.calendarMonth"
        :selected-date="props.selectedDate"
        :navigate-route-name="props.navigateRouteName"
        :navigate-query-key="props.navigateQueryKey"
        :density="props.density"
        :show-month-navigation="props.showMonthNavigation"
        :header-title="props.headerTitle"
        grid-caption-key="patient.medications.history.calendar.gridAria"
        :prev-month-aria-label="t('patient.medications.history.calendar.prevMonth')"
        :next-month-aria-label="t('patient.medications.history.calendar.nextMonth')"
        :open-day-details-aria-label="t('patient.medications.history.calendar.openDayDetails')"
        :day-aria-label="dayAriaLabel"
        @select-date="emit('selectDate', $event)"
    >
        <template #day-indicator="{ cell }">
            <MedicationIntakeCalendarDayIndicator
                :cell="cell"
                :days-by-date="daysByDate"
            />
        </template>

        <template #legend>
            <span class="inline-flex items-center gap-1.5 font-medium text-text-heading">
                <Check
                    class="size-4 shrink-0 stroke-2 text-success"
                    aria-hidden="true"
                />
                {{ t('patient.medications.history.status.complete') }}
            </span>
            <span class="inline-flex items-center gap-1.5 font-medium text-text-heading">
                <Minus
                    class="size-4 shrink-0 stroke-2 text-warning"
                    aria-hidden="true"
                />
                {{ t('patient.medications.history.status.partial') }}
            </span>
            <span class="inline-flex items-center gap-1.5 font-medium text-text-heading">
                <AlertCircle
                    class="size-4 shrink-0 stroke-2 text-danger"
                    aria-hidden="true"
                />
                {{ t('patient.medications.history.status.none_taken') }}
            </span>
        </template>
    </HistoryMonthCalendar>
</template>
