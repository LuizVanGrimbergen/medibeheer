<script setup lang="ts">
import {
    patientPageCardDetailIconWrapperClass,
    patientPageCardDetailLabelClass,
    patientPageCardDetailValueClass,
} from '@/lib/patient/patientPageTypography';

const props = withDefaults(
    defineProps<{
        label: string;
        /** Custom content in the value slot (times list, time element, …). */
        rawValue?: boolean;
    }>(),
    {
        rawValue: false,
    },
);
</script>

<template>
    <div class="flex gap-4 sm:gap-5">
        <div v-if="$slots.icon" :class="patientPageCardDetailIconWrapperClass">
            <slot name="icon" />
        </div>
        <div class="min-w-0 flex-1 space-y-1.5">
            <p :class="patientPageCardDetailLabelClass">
                {{ props.label }}
            </p>
            <slot v-if="props.rawValue" />
            <p v-else :class="patientPageCardDetailValueClass">
                <slot />
            </p>
        </div>
    </div>
</template>
