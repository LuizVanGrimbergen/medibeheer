<script setup lang="ts">
import { Frown, Meh, Smile } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import FamilyWellbeingCalendarDayIndicator from '@/Components/Family/Wellbeing/FamilyWellbeingCalendarDayIndicator.vue';
import HistoryMonthCalendar from '@/Components/History/HistoryMonthCalendar.vue';
import { formatHistoryCalendarLongDate } from '@/lib/history/formatHistoryCalendarDate';
import type { HistoryMonthCalendarCell } from '@/lib/history/historyMonthCalendarTypes';
import type { DailyMoodScoreValue } from '@/lib/types';

const props = withDefaults(
    defineProps<{
        calendarMonth: string;
        moodsByDate: Record<string, DailyMoodScoreValue>;
        selectedDate?: string | null;
        navigateRouteName?: string;
    }>(),
    {
        selectedDate: null,
        navigateRouteName: 'family.wellbeing',
    },
);

const emit = defineEmits<{
    selectDate: [dateKey: string];
}>();

const { t, locale } = useI18n();

function moodForCell(cell: HistoryMonthCalendarCell): DailyMoodScoreValue | null {
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
        grid-caption-key="family.wellbeing.calendar.gridAria"
        :prev-month-aria-label="t('family.wellbeing.calendar.prevMonth')"
        :next-month-aria-label="t('family.wellbeing.calendar.nextMonth')"
        :open-day-details-aria-label="t('family.wellbeing.calendar.openDayDetails')"
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
            <span class="inline-flex items-center gap-1.5 font-medium text-text-heading">
                <Frown
                    class="size-4 shrink-0 stroke-2 text-danger"
                    aria-hidden="true"
                />
                {{ t('family.wellbeing.mood.bad') }}
            </span>
            <span class="inline-flex items-center gap-1.5 font-medium text-text-heading">
                <Meh
                    class="size-4 shrink-0 stroke-2 text-warning"
                    aria-hidden="true"
                />
                {{ t('family.wellbeing.mood.ok') }}
            </span>
            <span class="inline-flex items-center gap-1.5 font-medium text-text-heading">
                <Smile
                    class="size-4 shrink-0 stroke-2 text-success"
                    aria-hidden="true"
                />
                {{ t('family.wellbeing.mood.good') }}
            </span>
        </template>
    </HistoryMonthCalendar>
</template>
