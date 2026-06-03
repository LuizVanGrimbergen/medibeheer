<script setup lang="ts">
import type { LucideIcon } from 'lucide-vue-next';
import { Frown, Meh, Smile } from 'lucide-vue-next';
import { computed } from 'vue';
import type { HistoryMonthCalendarCell } from '@/lib/history/historyMonthCalendarTypes';
import type { DailyMoodScoreValue } from '@/lib/types';
import { cn } from '@/lib/utils';

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

const glyph = computed((): LucideIcon | null => {
    if (mood.value === 'bad') {
        return Frown;
    }

    if (mood.value === 'ok') {
        return Meh;
    }

    if (mood.value === 'good') {
        return Smile;
    }

    return null;
});

const iconClass = computed((): string => {
    if (mood.value === 'bad') {
        return 'text-danger';
    }

    if (mood.value === 'ok') {
        return 'text-warning';
    }

    if (mood.value === 'good') {
        return 'text-success';
    }

    return 'text-border';
});
</script>

<template>
    <component
        v-if="glyph !== null"
        :is="glyph"
        :class="
            cn('size-[1.05rem] shrink-0 stroke-[2.25] sm:size-5', iconClass)
        "
        aria-hidden="true"
    />
    <span
        v-else
        class="bg-border size-1.5 rounded-full sm:size-2"
        aria-hidden="true"
    />
</template>
