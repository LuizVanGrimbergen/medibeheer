<script setup lang="ts">
import { computed } from 'vue';
import { MoodIcon } from '@/Components/ui/mood-icon';
import type { HistoryMonthCalendarCell } from '@/lib/history/historyMonthCalendarTypes';
import type { DailyMoodScoreValue } from '@/lib/types';

const props = defineProps<{
    cell: HistoryMonthCalendarCell;
    moodsByDate: Record<string, DailyMoodScoreValue>;
}>();

const mood = computed((): DailyMoodScoreValue | null => {
    if (props.cell.dateKey === null) {
        return null;
    }

    return props.moodsByDate[props.cell.dateKey] ?? null;
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
