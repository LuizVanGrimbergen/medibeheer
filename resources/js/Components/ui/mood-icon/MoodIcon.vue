<script setup lang="ts">
import { computed } from 'vue';
import type { HTMLAttributes } from 'vue';
import {
    dailyMoodFaceClass,
    dailyMoodIcon,
} from '@/lib/mood/dailyMoodPresentation';
import type { DailyMoodScoreValue } from '@/lib/types';
import { cn } from '@/lib/utils';

export type MoodIconSize = 'legend' | 'calendar-day' | 'card' | 'picker';

const MOOD_ICON_SIZE_CLASS: Record<MoodIconSize, string> = {
    legend: 'size-4 shrink-0 stroke-2',
    'calendar-day': 'size-[1.05rem] shrink-0 stroke-[2.25] sm:size-5',
    card: 'size-6 shrink-0 stroke-[1.75] sm:size-7',
    picker: 'size-12 stroke-[1.75] sm:size-14 md:size-16',
};

const props = withDefaults(
    defineProps<{
        mood: DailyMoodScoreValue;
        size?: MoodIconSize;
        class?: HTMLAttributes['class'];
    }>(),
    {
        size: 'calendar-day',
        class: undefined,
    },
);

const resolvedIcon = computed(() => dailyMoodIcon(props.mood));

const iconClass = computed(() =>
    cn(
        MOOD_ICON_SIZE_CLASS[props.size],
        dailyMoodFaceClass(props.mood),
        props.class,
    ),
);
</script>

<template>
    <component
        :is="resolvedIcon"
        :class="iconClass"
        aria-hidden="true"
    />
</template>
