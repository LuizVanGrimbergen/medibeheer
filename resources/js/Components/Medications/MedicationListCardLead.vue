<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationTypeLeadIcon from '@/Components/Medications/MedicationTypeLeadIcon.vue';
import type { MedicationUrgencyTone } from '@/lib/patient/medications/urgency/medicationUrgencyTone';
import { medicationUrgencyToneClasses } from '@/lib/patient/medications/urgency/medicationUrgencyToneClasses';
import type { MedicationTypeValue } from '@/lib/types';

const props = withDefaults(
    defineProps<{
        name: string;
        typeMedication: MedicationTypeValue;
        tone: MedicationUrgencyTone | null;
        showTypeLabel?: boolean;
    }>(),
    {
        showTypeLabel: true,
    },
);

const { t } = useI18n();

const typeLabel = computed(() =>
    t(`patient.medications.types.${props.typeMedication}`),
);

const visualToneClasses = computed(() =>
    medicationUrgencyToneClasses(props.tone),
);
</script>

<template>
    <div class="flex min-w-0 items-start gap-4">
        <div
            class="flex size-12 shrink-0 items-center justify-center rounded-xl"
            :class="visualToneClasses.pillWrap"
        >
            <span class="sr-only">{{ typeLabel }}</span>
            <MedicationTypeLeadIcon
                :medication-type="typeMedication"
                :icon-tone-class="visualToneClasses.pillIcon"
            />
        </div>
        <div class="min-w-0 flex-1 space-y-1 overflow-hidden">
            <p
                class="text-text-heading text-lg leading-snug font-bold sm:text-xl"
            >
                {{ name }}
            </p>
            <slot v-if="$slots.subtitle" name="subtitle" />
            <p
                v-else-if="showTypeLabel"
                class="text-text-muted text-base leading-snug font-normal"
            >
                {{ typeLabel }}
            </p>
        </div>
    </div>
</template>
