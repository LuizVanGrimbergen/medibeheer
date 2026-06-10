<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationTypeLeadIcon from '@/Components/shared/medications/MedicationTypeLeadIcon.vue';
import type { MedicationUrgencyTone } from '@/lib/patient/medications/urgency/medicationUrgencyTone';
import { medicationUrgencyToneClasses } from '@/lib/patient/medications/urgency/medicationUrgencyToneClasses';
import {
    mobileShellMedicationListCardLeadIconWrapClass,
    mobileShellMedicationListCardLeadRowClass,
    mobileShellMedicationListCardLeadTitleClass,
} from '@/lib/shell/mobileShellTypography';
import type { MedicationTypeValue } from '@/lib/types';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        name: string;
        typeMedication: MedicationTypeValue;
        tone: MedicationUrgencyTone | null;
        showTypeLabel?: boolean;
        alignTitleWithIcon?: boolean;
    }>(),
    {
        showTypeLabel: true,
        alignTitleWithIcon: false,
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
    <div
        :class="
            cn(
                mobileShellMedicationListCardLeadRowClass,
                alignTitleWithIcon && 'items-center',
            )
        "
    >
        <div
            :class="
                cn(
                    mobileShellMedicationListCardLeadIconWrapClass,
                    visualToneClasses.pillWrap,
                )
            "
        >
            <span class="sr-only">{{ typeLabel }}</span>
            <MedicationTypeLeadIcon
                :medication-type="typeMedication"
                :icon-tone-class="visualToneClasses.pillIcon"
            />
        </div>
        <div class="min-w-0 flex-1 space-y-1 overflow-hidden">
            <slot v-if="$slots['title-before']" name="title-before" />
            <p :class="mobileShellMedicationListCardLeadTitleClass">
                {{ name }}
            </p>
            <slot v-if="$slots['title-after']" name="title-after" />
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
