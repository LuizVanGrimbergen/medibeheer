<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import HistoryMonthCalendar from '@/Components/History/HistoryMonthCalendar.vue';
import MedicationIntakeCalendarDayIndicator from '@/Components/Patient/Medications/MedicationIntakeCalendarDayIndicator.vue';
import { MedicationIntakeDayLegend } from '@/Components/ui/medication-intake-day-icon';
import {
    formatHistoryCalendarLongDate,
    todayIsoDateKey,
} from '@/lib/history/formatHistoryCalendarDate';
import type { HistoryMonthCalendarCell } from '@/lib/history/historyMonthCalendarTypes';
import { indexMedicationIntakeCalendarDays } from '@/lib/patient/medications/history/indexMedicationIntakeCalendarDays';
import {
    isMedicationIntakeDayIconStatus
    
} from '@/lib/patient/medications/history/medicationIntakeDayPresentation';
import type {MedicationIntakeDayIconStatusValue} from '@/lib/patient/medications/history/medicationIntakeDayPresentation';
import type { MedicationIntakeCalendarDay } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';
import { cn } from '@/lib/utils';

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
        statusFilter?: MedicationIntakeDayIconStatusValue | null;
        statusCounts?: Record<MedicationIntakeDayIconStatusValue, number>;
        selectableLegendStatuses?: readonly MedicationIntakeDayIconStatusValue[];
    }>(),
    {
        selectedDate: null,
        navigateQueryKey: 'calendar_month',
        density: 'default',
        showMonthNavigation: true,
        headerTitle: undefined,
        statusFilter: null,
        statusCounts: undefined,
        selectableLegendStatuses: undefined,
    },
);

const emit = defineEmits<{
    selectDate: [dateKey: string];
    selectStatusFilter: [status: MedicationIntakeDayIconStatusValue];
}>();

const hasSelectableLegend = computed(
    (): boolean => props.statusCounts !== undefined,
);

const { t, locale } = useI18n();

const daysByDate = computed(() =>
    indexMedicationIntakeCalendarDays(props.calendarDays),
);

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
        return t('patient.medications.history.calendar.dayNoSchedule', {
            date: longDate,
        });
    }

    if (day.status === 'none_taken' && day.date > todayKey) {
        return t('patient.medications.history.calendar.dayNoIntake', {
            date: longDate,
        });
    }

    const displayedStatus = day.status;

    if (
        props.statusFilter !== null &&
        isMedicationIntakeDayIconStatus(displayedStatus) &&
        displayedStatus !== props.statusFilter
    ) {
        return t('patient.medications.history.calendar.dayNoSchedule', {
            date: longDate,
        });
    }

    return t('patient.medications.history.calendar.dayWithStatus', {
        date: longDate,
        status: t(`patient.medications.history.status.${displayedStatus}`),
    });
}
</script>

<template>
    <div
        :class="
            cn('flex min-w-0 flex-col', hasSelectableLegend ? 'gap-2' : 'gap-0')
        "
    >
        <HistoryMonthCalendar
            :calendar-month="props.calendarMonth"
            :selected-date="props.selectedDate"
            :navigate-route-name="props.navigateRouteName"
            :navigate-query-key="props.navigateQueryKey"
            :density="props.density"
            :show-month-navigation="props.showMonthNavigation"
            :header-title="props.headerTitle"
            grid-caption-key="patient.medications.history.calendar.gridAria"
            :prev-month-aria-label="
                t('patient.medications.history.calendar.prevMonth')
            "
            :next-month-aria-label="
                t('patient.medications.history.calendar.nextMonth')
            "
            :open-day-details-aria-label="
                t('patient.medications.history.calendar.openDayDetails')
            "
            :day-aria-label="dayAriaLabel"
            @select-date="emit('selectDate', $event)"
        >
            <template #day-indicator="{ cell }">
                <MedicationIntakeCalendarDayIndicator
                    :cell="cell"
                    :days-by-date="daysByDate"
                    :status-filter="props.statusFilter"
                />
            </template>

            <template v-if="!hasSelectableLegend" #legend>
                <MedicationIntakeDayLegend />
            </template>
        </HistoryMonthCalendar>

        <fieldset
            v-if="hasSelectableLegend"
            class="w-full min-w-0 border-0 p-0"
        >
            <legend class="sr-only">
                {{ t('doctor.patients.medicationLegendFilter') }}
            </legend>

            <MedicationIntakeDayLegend
                :statuses="props.selectableLegendStatuses"
                presentation="filter"
                selectable
                :selected-status="props.statusFilter"
                :status-counts="props.statusCounts"
                @select-status="emit('selectStatusFilter', $event)"
            />
        </fieldset>
    </div>
</template>
