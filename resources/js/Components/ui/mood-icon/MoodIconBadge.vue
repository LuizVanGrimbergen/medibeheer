<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import MoodIcon from '@/Components/ui/mood-icon/MoodIcon.vue';
import { dailyMoodPresentation } from '@/lib/mood/dailyMoodPresentation';
import type { DailyMoodScoreValue } from '@/lib/types';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        mood: DailyMoodScoreValue;
        class?: HTMLAttributes['class'];
    }>(),
    {
        class: undefined,
    },
);

const presentation = dailyMoodPresentation(props.mood);
</script>

<template>
    <div
        :class="
            cn(
                'flex size-12 shrink-0 items-center justify-center rounded-2xl sm:size-14',
                presentation.iconBackgroundClass,
                props.class,
            )
        "
    >
        <MoodIcon :mood="props.mood" size="card" />
    </div>
</template>
