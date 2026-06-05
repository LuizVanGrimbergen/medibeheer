<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import FamilyWellbeingCalendarDayIndicator from '@/Components/Family/Wellbeing/FamilyWellbeingCalendarDayIndicator.vue';
import HistoryMonthCalendar from '@/Components/History/HistoryMonthCalendar.vue';
import { MoodLegend } from '@/Components/ui/mood-icon';
import { formatHistoryCalendarLongDate } from '@/lib/history/formatHistoryCalendarDate';
import type { HistoryMonthCalendarCell } from '@/lib/history/historyMonthCalendarTypes';
import type { DailyMoodScoreValue } from '@/lib/types';

const props = withDefaults(
    defineProps<{
        calendarMonth: string;
        moodsByDate: Record<string, DailyMoodScoreValue>;
        selectedDate?: string | null;
        navigateRouteName?: string;
        density?: 'default' | 'compact';
        showMonthNavigation?: boolean;
        headerTitle?: string;
    }>(),
    {
        selectedDate: null,
        navigateRouteName: 'family.wellbeing',
        density: 'default',
        showMonthNavigation: true,
        headerTitle: undefined,
    },
);

const emit = defineEmits<{
    selectDate: [dateKey: string];
}>();

const { t, locale } = useI18n();

function moodForCell(
    cell: HistoryMonthCalendarCell,
): DailyMoodScoreValue | null {
    if (cell.dateKey === null) {
        return null;
    }

    return props.moodsByDate[cell.dateKey] ?? null;
}

function dayAriaLabel(cell: HistoryMonthCalendarCell): string {
    if (cell.dateKey === null) {
        return '';
    }

    const longDate = formatHistoryCalendarLongDate(cell.dateKey, locale.value);
    const mood = moodForCell(cell);

    if (mood === null) {
        return t('family.wellbeing.calendar.dayNoCheckin', { date: longDate });
    }

    return t('family.wellbeing.calendar.dayWithMood', {
        date: longDate,
        mood: t(`family.wellbeing.mood.${mood}`),
    });
}
</script>

<template>
    <HistoryMonthCalendar
        :calendar-month="props.calendarMonth"
        :selected-date="props.selectedDate"
        :navigate-route-name="props.navigateRouteName"
        :density="props.density"
        :show-month-navigation="props.showMonthNavigation"
        :header-title="props.headerTitle"
        grid-caption-key="family.wellbeing.calendar.gridAria"
        :prev-month-aria-label="t('family.wellbeing.calendar.prevMonth')"
        :next-month-aria-label="t('family.wellbeing.calendar.nextMonth')"
        :open-day-details-aria-label="
            t('family.wellbeing.calendar.openDayDetails')
        "
        :day-aria-label="dayAriaLabel"
        @select-date="emit('selectDate', $event)"
    >
        <template #day-indicator="{ cell }">
            <FamilyWellbeingCalendarDayIndicator
                :cell="cell"
                :moods-by-date="props.moodsByDate"
            />
        </template>

        <template #legend>
            <MoodLegend />
        </template>
    </HistoryMonthCalendar>
</template>
