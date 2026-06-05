<script setup lang="ts">
import { Layers } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { medicationListVisualTone } from '@/lib/patient/inventory/medicationListVisualTone';
import { formatMedicationStockDisplayAmount } from '@/lib/patient/medications/stock/formatMedicationStockDisplayAmount';
import { medicationStockDisplayDoseUnit } from '@/lib/patient/medications/stock/medicationStockDisplayDoseUnit';
import { parseMedicationStrengthFromStored } from '@/lib/patient/medications/strength/parseMedicationStrengthFromStored';
import {
    medicationUrgencyPanelClass,
    medicationUrgencyPanelIconWrapClass,
} from '@/lib/patient/medications/urgency/medicationUrgencyPanelClasses';
import type { MedicationListItem } from '@/lib/types';

const props = defineProps<{
    medication: MedicationListItem;
}>();

const { t } = useI18n();

const primaryStock = computed(() => props.medication.stocks[0]);

const stockProgressTone = computed(() =>
    medicationListVisualTone(props.medication),
);

const currentStockPanelClass = computed((): string =>
    medicationUrgencyPanelClass(stockProgressTone.value),
);

const currentStockIconWrapClass = computed((): string =>
    medicationUrgencyPanelIconWrapClass(stockProgressTone.value),
);

const primaryStockAmountTrimmed = computed(
    (): string => primaryStock.value?.current_stock.trim() ?? '',
);

const stockDisplayDoseUnit = computed(() => {
    const parsedStrength = parseMedicationStrengthFromStored(
        props.medication.strength,
    );

    return medicationStockDisplayDoseUnit(
        props.medication.dose_unit,
        parsedStrength.strength_unit,
    );
});

const currentStockDisplayLine = computed((): string =>
    formatMedicationStockDisplayAmount(
        t,
        primaryStockAmountTrimmed.value,
        stockDisplayDoseUnit.value,
    ),
);
</script>

<template>
    <div
        v-if="primaryStock !== undefined"
        class="flex w-full min-w-0 justify-start"
    >
        <div class="min-w-0 flex-1" :class="currentStockPanelClass">
            <div :class="currentStockIconWrapClass">
                <Layers class="size-5 sm:size-6" aria-hidden="true" />
            </div>
            <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                <span
                    class="text-text-heading text-sm leading-snug font-semibold sm:text-base"
                >
                    {{ t('patient.medications.fields.currentStock') }}
                </span>
                <span
                    class="text-text-heading text-2xl leading-none font-bold tracking-tight wrap-break-word whitespace-pre-wrap tabular-nums sm:text-3xl"
                >
                    {{ currentStockDisplayLine }}
                </span>
            </div>
        </div>
    </div>
</template>
