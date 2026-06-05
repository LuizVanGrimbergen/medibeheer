<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyWellbeingCalendarDayIndicator from '@/Components/Family/Wellbeing/FamilyWellbeingCalendarDayIndicator.vue';
import HistoryMonthCalendar from '@/Components/History/HistoryMonthCalendar.vue';
import { MoodLegend } from '@/Components/ui/mood-icon';
import { formatHistoryCalendarLongDate } from '@/lib/history/formatHistoryCalendarDate';
import type { HistoryMonthCalendarCell } from '@/lib/history/historyMonthCalendarTypes';
import {
    isNotableDailyMood,
    NOTABLE_DAILY_MOOD_SCORE_VALUES,
} from '@/lib/mood/isNotableDailyMood';
import type { DailyMoodScoreValue } from '@/lib/types';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        calendarMonth: string;
        moodsByDate: Record<string, DailyMoodScoreValue>;
        selectedDate?: string | null;
        navigateRouteName?: string;
        density?: 'default' | 'compact';
        showMonthNavigation?: boolean;
        headerTitle?: string;
        notableMoodsOnly?: boolean;
        dayNoNotableCheckinKey?: string;
        moodFilter?: DailyMoodScoreValue | null;
        moodCounts?: Record<DailyMoodScoreValue, number>;
        selectableLegendMoods?: readonly DailyMoodScoreValue[];
    }>(),
    {
        selectedDate: null,
        navigateRouteName: 'family.wellbeing',
        density: 'default',
        showMonthNavigation: true,
        headerTitle: undefined,
        notableMoodsOnly: false,
        dayNoNotableCheckinKey: undefined,
        moodFilter: null,
        moodCounts: undefined,
        selectableLegendMoods: undefined,
    },
);

const emit = defineEmits<{
    selectDate: [dateKey: string];
    selectMoodFilter: [mood: DailyMoodScoreValue];
}>();

const hasSelectableLegend = computed(
    (): boolean => props.moodCounts !== undefined,
);

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
        if (
            props.notableMoodsOnly &&
            props.dayNoNotableCheckinKey !== undefined
        ) {
            return t(props.dayNoNotableCheckinKey, { date: longDate });
        }

        return t('family.wellbeing.calendar.dayNoCheckin', { date: longDate });
    }

    let displayedMood =
        props.notableMoodsOnly && !isNotableDailyMood(mood) ? null : mood;

    if (
        displayedMood !== null &&
        props.moodFilter !== null &&
        displayedMood !== props.moodFilter
    ) {
        displayedMood = null;
    }

    if (displayedMood === null) {
        if (props.dayNoNotableCheckinKey !== undefined) {
            return t(props.dayNoNotableCheckinKey, { date: longDate });
        }

        return t('family.wellbeing.calendar.dayNoCheckin', { date: longDate });
    }

    return t('family.wellbeing.calendar.dayWithMood', {
        date: longDate,
        mood: t(`family.wellbeing.mood.${displayedMood}`),
    });
}
</script>

<template>
    <div
        :class="
            cn(
                'flex min-w-0 flex-col',
                hasSelectableLegend ? 'gap-2' : 'gap-0',
            )
        "
    >
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
                    :notable-moods-only="props.notableMoodsOnly"
                    :mood-filter="props.moodFilter"
                />
            </template>

            <template v-if="!hasSelectableLegend" #legend>
                <MoodLegend
                    :moods="
                        props.notableMoodsOnly
                            ? NOTABLE_DAILY_MOOD_SCORE_VALUES
                            : undefined
                    "
                />
            </template>
        </HistoryMonthCalendar>

        <fieldset
            v-if="hasSelectableLegend"
            class="w-full min-w-0 border-0 p-0"
        >
            <legend class="sr-only">
                {{ t('doctor.patients.wellbeingLegendFilter') }}
            </legend>

            <MoodLegend
                :moods="props.selectableLegendMoods"
                presentation="filter"
                selectable
                :selected-mood="props.moodFilter"
                :mood-counts="props.moodCounts"
                @select-mood="emit('selectMoodFilter', $event)"
            />
        </fieldset>
    </div>
</template>
