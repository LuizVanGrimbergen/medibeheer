<script setup lang="ts">
import { computed } from 'vue';
import type { HTMLAttributes } from 'vue';
import {
    medicationIntakeDayFaceClass,
    medicationIntakeDayIcon,
    type MedicationIntakeDayIconStatusValue,
} from '@/lib/patient/medications/history/medicationIntakeDayPresentation';
import { cn } from '@/lib/utils';

export type MedicationIntakeDayIconSize = 'legend' | 'calendar-day' | 'card';

const MEDICATION_INTAKE_DAY_ICON_SIZE_CLASS: Record<
    MedicationIntakeDayIconSize,
    string
> = {
    legend: 'size-4 shrink-0 stroke-2',
    'calendar-day': 'size-[1.05rem] shrink-0 stroke-[2.25] sm:size-5',
    card: 'size-6 shrink-0 stroke-[1.75] sm:size-7',
};

const props = withDefaults(
    defineProps<{
        status: MedicationIntakeDayIconStatusValue;
        size?: MedicationIntakeDayIconSize;
        class?: HTMLAttributes['class'];
    }>(),
    {
        size: 'calendar-day',
        class: undefined,
    },
);

const resolvedIcon = computed(() => medicationIntakeDayIcon(props.status));

const iconClass = computed(() =>
    cn(
        MEDICATION_INTAKE_DAY_ICON_SIZE_CLASS[props.size],
        medicationIntakeDayFaceClass(props.status),
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
