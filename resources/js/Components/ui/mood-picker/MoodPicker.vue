<script setup lang="ts">
import { computed } from 'vue';
import type { HTMLAttributes } from 'vue';
import { useI18n } from 'vue-i18n';
import { MoodIcon } from '@/Components/ui/mood-icon';
import { DAILY_MOOD_OPTIONS } from '@/lib/mood/dailyMoodPresentation';
import type { DailyMoodScoreValue } from '@/lib/types';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        modelValue: DailyMoodScoreValue | null;
        disabled?: boolean;
        class?: HTMLAttributes['class'];
    }>(),
    {
        disabled: false,
        class: undefined,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: DailyMoodScoreValue | null];
}>();

const { t } = useI18n();

function isSelected(value: DailyMoodScoreValue): boolean {
    return props.modelValue === value;
}

const optionsForTemplate = computed(() => DAILY_MOOD_OPTIONS);
</script>

<template>
    <fieldset
        :class="
            cn(
                'mx-auto grid w-full max-w-xl grid-cols-3 items-stretch justify-items-center gap-2.5 rounded-2xl px-2 py-2 sm:gap-3 sm:rounded-3xl sm:px-3 sm:py-3 md:max-w-none md:flex md:w-auto md:items-center md:justify-center md:gap-12 md:px-6 md:py-4',
                props.class,
            )
        "
    >
        <legend class="sr-only">Mood</legend>

        <label
            v-for="mood in optionsForTemplate"
            :key="mood.mood"
            :class="
                cn(
                    'group flex min-h-28 w-full cursor-pointer flex-col items-center gap-2 rounded-2xl px-2.5 py-3 text-center transition-colors focus-within:ring-2 focus-within:ring-focus/25 sm:min-h-32 sm:gap-2.5 sm:px-3 sm:py-4 md:min-h-36 md:w-auto md:gap-3 md:px-6 md:py-5',
                    disabled && 'cursor-not-allowed opacity-60',
                    isSelected(mood.mood) && 'bg-surface-hover',
                )
            "
        >
            <input
                class="sr-only"
                type="radio"
                name="mood"
                :value="mood.mood"
                :disabled="disabled"
                :checked="isSelected(mood.mood)"
                @change="emit('update:modelValue', mood.mood)"
            />
            <MoodIcon :mood="mood.mood" size="picker" />
            <span
                class="text-sm font-semibold leading-snug text-text-muted sm:text-base md:text-lg"
                :class="isSelected(mood.mood) && 'text-text'"
            >
                {{ t(mood.labelKey) }}
            </span>
        </label>
    </fieldset>
</template>
