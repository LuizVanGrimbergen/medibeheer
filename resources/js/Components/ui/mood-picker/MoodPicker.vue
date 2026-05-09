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
                'mx-auto grid w-full max-w-md grid-cols-3 items-stretch justify-items-center gap-1.5 rounded-xl px-1 py-1.5 sm:gap-2 sm:rounded-2xl sm:px-2 sm:py-2 md:max-w-none md:flex md:w-auto md:items-center md:justify-center md:gap-10 md:px-4 md:py-3',
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
                    'group flex w-full cursor-pointer flex-col items-center gap-1 rounded-xl px-1.5 py-2 text-center transition-colors focus-within:ring-2 focus-within:ring-focus/25 sm:gap-1.5 sm:rounded-2xl sm:px-2 sm:py-3 md:w-auto md:gap-2 md:px-5 md:py-4',
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
                :class="cn('size-9 stroke-[1.75] sm:size-11 md:size-14', mood.faceClass)"
                aria-hidden="true"
            />
            <span
                class="text-[11px] font-medium leading-tight text-text-muted sm:text-xs md:text-sm"
                :class="isSelected(mood.value) && 'text-text'"
            >
                {{ mood.label }}
            </span>
        </label>
    </fieldset>
</template>

