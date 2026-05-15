<script setup lang="ts">
import { Pill } from 'lucide-vue-next';
import { computed } from 'vue';
import { medicationTypeIcon } from '@/lib/patient/medications/options/medicationTypeIcons';
import type { MedicationTypeValue } from '@/lib/types';
import { MEDICATION_TYPE_VALUES } from '@/lib/types';

const props = defineProps<{
    medicationType: string;
    iconToneClass?: string;
}>();

const resolvedIcon = computed(() => {
    const raw = props.medicationType.trim();

    if ((MEDICATION_TYPE_VALUES as readonly string[]).includes(raw)) {
        return medicationTypeIcon(raw as MedicationTypeValue);
    }

    return Pill;
});
</script>

<template>
    <component
        :is="resolvedIcon"
        class="size-6 shrink-0 sm:size-8"
        :class="iconToneClass"
        aria-hidden="true"
    />
</template>
