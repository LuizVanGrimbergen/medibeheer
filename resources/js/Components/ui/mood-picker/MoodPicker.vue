<script setup lang="ts">
import { Frown, Meh, Smile } from 'lucide-vue-next';
import { computed } from 'vue';
import type { HTMLAttributes } from 'vue';
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

type MoodOption = {
    value: DailyMoodScoreValue;
    icon: typeof Frown;
    faceClass: string;
    label: string;
};

const moodOptions: readonly MoodOption[] = [
    {
        value: 'bad',
        icon: Frown,
        faceClass: 'text-danger',
        label: 'Slecht',
    },
    {
        value: 'ok',
        icon: Meh,
        faceClass: 'text-warning',
        label: 'Gaat wel',
    },
    {
        value: 'good',
        icon: Smile,
        faceClass: 'text-success',
        label: 'Goed',
    },
] as const;

function isSelected(value: DailyMoodScoreValue): boolean {
    return props.modelValue === value;
}

const optionsForTemplate = computed(() => moodOptions);
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
            :key="mood.value"
            :class="
                cn(
                    'group flex min-h-28 w-full cursor-pointer flex-col items-center gap-2 rounded-2xl px-2.5 py-3 text-center transition-colors focus-within:ring-2 focus-within:ring-focus/25 sm:min-h-32 sm:gap-2.5 sm:px-3 sm:py-4 md:min-h-36 md:w-auto md:gap-3 md:px-6 md:py-5',
                    disabled && 'cursor-not-allowed opacity-60',
                    isSelected(mood.value) && 'bg-surface-hover',
                )
            "
        >
            <input
                class="sr-only"
                type="radio"
                name="mood"
                :value="mood.value"
                :disabled="disabled"
                :checked="isSelected(mood.value)"
                @change="emit('update:modelValue', mood.value)"
            />
            <component
                :is="mood.icon"
                :class="cn('size-12 stroke-[1.75] sm:size-14 md:size-16', mood.faceClass)"
                aria-hidden="true"
            />
            <span
                class="text-sm font-semibold leading-snug text-text-muted sm:text-base md:text-lg"
                :class="isSelected(mood.value) && 'text-text'"
            >
                {{ mood.label }}
            </span>
        </label>
    </fieldset>
</template>

