<script setup lang="ts">
import { computed } from 'vue';
import { MoodIcon } from '@/Components/ui/mood-icon';
import type { HistoryMonthCalendarCell } from '@/lib/history/historyMonthCalendarTypes';
import { isNotableDailyMood } from '@/lib/mood/isNotableDailyMood';
import type { DailyMoodScoreValue } from '@/lib/types';

const props = withDefaults(
    defineProps<{
        cell: HistoryMonthCalendarCell;
        moodsByDate: Record<string, DailyMoodScoreValue>;
        notableMoodsOnly?: boolean;
        moodFilter?: DailyMoodScoreValue | null;
    }>(),
    {
        notableMoodsOnly: false,
        moodFilter: null,
    },
);

const mood = computed((): DailyMoodScoreValue | null => {
    if (props.cell.dateKey === null) {
        return null;
    }

    const value = props.moodsByDate[props.cell.dateKey] ?? null;

    if (value === null) {
        return null;
    }

    if (props.notableMoodsOnly && !isNotableDailyMood(value)) {
        return null;
    }

    if (props.moodFilter !== null && value !== props.moodFilter) {
        return null;
    }

    return value;
});
</script>

<template>
    <MoodIcon
        v-if="mood !== null"
        :mood="mood"
        size="calendar-day"
    />
    <span
        v-else
        class="bg-border size-1.5 rounded-full sm:size-2"
        aria-hidden="true"
    />
</template>
