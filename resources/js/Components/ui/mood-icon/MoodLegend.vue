<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MoodIcon from '@/Components/ui/mood-icon/MoodIcon.vue';
import { DAILY_MOOD_OPTIONS } from '@/lib/mood/dailyMoodPresentation';
import type { DailyMoodScoreValue } from '@/lib/types';
import { cn } from '@/lib/utils';

export type MoodLegendPresentation = 'legend' | 'filter';

const props = withDefaults(
    defineProps<{
        moods?: readonly DailyMoodScoreValue[];
        selectable?: boolean;
        selectedMood?: DailyMoodScoreValue | null;
        moodCounts?: Record<DailyMoodScoreValue, number>;
        selectLabelKey?: string;
        presentation?: MoodLegendPresentation;
    }>(),
    {
        moods: undefined,
        selectable: false,
        selectedMood: null,
        moodCounts: undefined,
        selectLabelKey: 'doctor.patients.snapshotMoodSelect',
        presentation: 'legend',
    },
);

const emit = defineEmits<{
    selectMood: [mood: DailyMoodScoreValue];
}>();

const { t } = useI18n();

const legendOptions = computed(() => {
    if (props.moods === undefined) {
        return DAILY_MOOD_OPTIONS;
    }

    return DAILY_MOOD_OPTIONS.filter((option) =>
        props.moods!.includes(option.mood),
    );
});

function isMoodDisabled(mood: DailyMoodScoreValue): boolean {
    if (props.moodCounts === undefined) {
        return false;
    }

    return props.moodCounts[mood] === 0;
}

function selectedRingClass(mood: DailyMoodScoreValue): string {
    if (props.selectedMood !== mood) {
        return '';
    }

    if (mood === 'good') {
        return 'ring-success';
    }

    if (mood === 'ok') {
        return 'ring-warning';
    }

    return 'ring-danger';
}
</script>

<template>
    <div
        v-if="props.selectable && props.presentation === 'filter'"
        class="bg-surface-2 border-border flex w-full gap-0.5 rounded-lg border p-0.5"
    >
        <button
            v-for="option in legendOptions"
            :key="option.mood"
            type="button"
            :disabled="isMoodDisabled(option.mood)"
            :class="
                cn(
                    'flex min-w-0 flex-1 items-center justify-center gap-1 rounded-md px-1.5 py-1 text-center transition-all',
                    option.iconBackgroundClass,
                    !isMoodDisabled(option.mood) &&
                        'hover:ring-border cursor-pointer hover:ring-1',
                    props.selectedMood === option.mood
                        ? 'ring-1 shadow-sm'
                        : 'opacity-75',
                    selectedRingClass(option.mood),
                    isMoodDisabled(option.mood) && 'cursor-default opacity-40',
                )
            "
            :aria-label="
                t(props.selectLabelKey, {
                    mood: t(option.labelKey),
                })
            "
            :aria-pressed="props.selectedMood === option.mood"
            @click="emit('selectMood', option.mood)"
        >
            <MoodIcon :mood="option.mood" size="calendar-day" />
            <span
                v-if="props.moodCounts !== undefined"
                :class="
                    cn(
                        'text-sm font-semibold tabular-nums leading-none',
                        option.faceClass,
                    )
                "
            >
                {{ props.moodCounts[option.mood] }}
            </span>
        </button>
    </div>

    <template v-else-if="props.selectable">
        <button
            v-for="option in legendOptions"
            :key="option.mood"
            type="button"
            :disabled="isMoodDisabled(option.mood)"
            :class="
                cn(
                    'inline-flex items-center gap-1.5 rounded-lg px-2 py-1 font-medium transition-shadow',
                    option.faceClass,
                    !isMoodDisabled(option.mood) &&
                        'hover:ring-border cursor-pointer hover:ring-2',
                    props.selectedMood === option.mood &&
                        'ring-2 ring-offset-1',
                    selectedRingClass(option.mood),
                    isMoodDisabled(option.mood) && 'cursor-default opacity-50',
                )
            "
            :aria-label="
                t(props.selectLabelKey, {
                    mood: t(option.labelKey),
                })
            "
            :aria-pressed="props.selectedMood === option.mood"
            @click="emit('selectMood', option.mood)"
        >
            <MoodIcon :mood="option.mood" size="legend" />
            {{ t(option.labelKey) }}
        </button>
    </template>

    <template v-else>
        <span
            v-for="option in legendOptions"
            :key="option.mood"
            class="text-text-heading inline-flex items-center gap-1.5 font-medium"
        >
            <MoodIcon :mood="option.mood" size="legend" />
            {{ t(option.labelKey) }}
        </span>
    </template>
</template>
