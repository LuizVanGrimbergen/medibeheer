<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationCurrentStockPanel from '@/Components/shared/medications/MedicationCurrentStockPanel.vue';
import MedicationTypeLeadIcon from '@/Components/shared/medications/MedicationTypeLeadIcon.vue';
import MedicationUrgencyProgressSection from '@/Components/shared/medications/MedicationUrgencyProgressSection.vue';
import {
    medicationUrgencyStatusTextClass,
    type MedicationUrgencyTone,
} from '@/lib/patient/medications/urgency/medicationUrgencyTone';
import {
    mobileShellPageCardHeaderSummaryClass,
    mobileShellPageCardHeaderWithActionsClass,
} from '@/lib/shell/mobileShellTypography';
import type { MedicationRegisterItem, MedicationTypeValue } from '@/lib/types';
import { cn } from '@/lib/utils';

const props = defineProps<{
    medication: MedicationRegisterItem;
    isOpen: boolean;
    hasEditOrDeleteActions: boolean;
    typeLabel: string;
    listStatusLabel: string | null;
    collapsedHeaderLine: string;
    showCollapsedUrgencyAlert: boolean;
    showCollapsedSupplyDaysSummary: boolean;
    stockProgressTone: MedicationUrgencyTone | null;
    stockProgressPercent: number | null;
    supplyEstimateLine: string;
    stockProgressAriaLabel: string;
    medicationPillWrapToneClass: string;
    medicationPillIconClass: string;
}>();

const { t } = useI18n();

const medicationType = computed(
    (): MedicationTypeValue => props.medication.type_medication,
);
</script>

<template>
    <div
        class="flex min-w-0 items-start gap-4"
        :class="
            hasEditOrDeleteActions
                ? mobileShellPageCardHeaderWithActionsClass
                : null
        "
    >
        <div
            class="flex size-12 shrink-0 items-center justify-center rounded-xl"
            :class="medicationPillWrapToneClass"
            aria-hidden="true"
        >
            <span class="sr-only">{{ typeLabel }}</span>
            <MedicationTypeLeadIcon
                :medication-type="medicationType"
                :icon-tone-class="medicationPillIconClass"
            />
        </div>
        <div class="min-w-0 flex-1 space-y-1.5">
            <p
                class="text-text-heading text-lg leading-snug font-bold sm:text-xl"
            >
                {{ medication.name }}
            </p>
            <p
                v-if="!isOpen && !showCollapsedUrgencyAlert"
                :class="
                    cn(
                        mobileShellPageCardHeaderSummaryClass,
                        showCollapsedSupplyDaysSummary &&
                            medicationUrgencyStatusTextClass(stockProgressTone),
                    )
                "
            >
                {{ collapsedHeaderLine }}
            </p>
            <p
                v-if="listStatusLabel !== null"
                class="text-text-muted text-base leading-snug font-semibold"
            >
                {{ listStatusLabel }}
            </p>
        </div>
    </div>

    <div
        v-if="!isOpen && showCollapsedUrgencyAlert"
        class="mt-3.5 space-y-3.5"
    >
        <MedicationUrgencyProgressSection
            :tone="stockProgressTone"
            :progress-percent="stockProgressPercent"
            :status-line="supplyEstimateLine"
            :progress-aria-label="stockProgressAriaLabel"
            :critical-alert-label="t('patient.inventory.lowStockBadge')"
            :warning-alert-label="t('patient.inventory.warningStockIconAria')"
        />
        <MedicationCurrentStockPanel :medication="medication" />
    </div>
</template>
