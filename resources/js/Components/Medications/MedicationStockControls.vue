<script setup lang="ts">
import { Layers, PackagePlus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationUrgencyProgressSection from '@/Components/Medications/MedicationUrgencyProgressSection.vue';
import MedicationInventoryStockEditDialog from '@/Components/Patient/Inventory/form/MedicationInventoryStockEditDialog.vue';
import { Button } from '@/Components/ui/button';
import { medicationListVisualTone } from '@/lib/patient/inventory/medicationListVisualTone';
import { medicationStockProgressPercent } from '@/lib/patient/inventory/medicationStockProgressPercent';
import { formatMedicationStockDisplayAmount } from '@/lib/patient/medications/stock/formatMedicationStockDisplayAmount';
import { medicationStockDisplayDoseUnit } from '@/lib/patient/medications/stock/medicationStockDisplayDoseUnit';
import { parseMedicationStrengthFromStored } from '@/lib/patient/medications/strength/parseMedicationStrengthFromStored';
import {
    medicationUrgencyOutlineButtonClass,
    medicationUrgencyPanelClass,
    medicationUrgencyPanelIconWrapClass,
} from '@/lib/patient/medications/urgency/medicationUrgencyPanelClasses';
import { patientShellDialogContentClass } from '@/lib/patient/patientShellDialogLayout';
import type { MedicationListItem } from '@/lib/types';

const props = withDefaults(
    defineProps<{
        medication: MedicationListItem;
        updateRouteName?: string;
        idPrefix?: string;
        canAdjustStock?: boolean;
    }>(),
    {
        updateRouteName: 'patient.medications.stocks.update',
        canAdjustStock: true,
    },
);

const { t } = useI18n();

const stockEditOpen = ref(false);

const dialogContentClass = patientShellDialogContentClass('md');

const primaryStock = computed(() => props.medication.stocks[0]);

const resolvedIdPrefix = computed(
    () => props.idPrefix ?? `medication-stock-${props.medication.id}`,
);

const stockFormId = computed(() => `${resolvedIdPrefix.value}-form`);

const stockProgressTone = computed(() => medicationListVisualTone(props.medication));

const stockProgressPercent = computed((): number | null => {
    const stock = primaryStock.value;

    if (stock === undefined) {
        return null;
    }

    return medicationStockProgressPercent(
        props.medication.supply_estimate_days,
        props.medication.supply_estimate_quality,
    );
});

const adjustStockButtonClass = computed((): string =>
    medicationUrgencyOutlineButtonClass(stockProgressTone.value),
);

const currentStockPanelClass = computed((): string =>
    medicationUrgencyPanelClass(stockProgressTone.value),
);

const currentStockIconWrapClass = computed((): string =>
    medicationUrgencyPanelIconWrapClass(stockProgressTone.value),
);

const supplyEstimateLine = computed((): string => {
    const days = props.medication.supply_estimate_days;
    const quality = props.medication.supply_estimate_quality;

    if (quality === 'approx' && days !== null) {
        if (days < 1) {
            return t('patient.inventory.supplyEstimateApproxLessThanDay');
        }

        if (days === 1) {
            return t('patient.inventory.supplyEstimateApproxOneDay');
        }

        return t('patient.inventory.supplyEstimateApproxDays', { days: String(days) });
    }

    return t('patient.inventory.supplyEstimateUnknown');
});

const stockProgressAriaLabel = computed((): string =>
    t('patient.inventory.stockProgressAria', {
        days: String(props.medication.supply_estimate_days ?? 0),
    }),
);

const primaryStockAmountTrimmed = computed((): string => primaryStock.value?.current_stock.trim() ?? '');

const stockDisplayDoseUnit = computed(() => {
    const parsedStrength = parseMedicationStrengthFromStored(props.medication.strength);

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
    <div class="space-y-3.5">
        <template v-if="primaryStock !== undefined">
            <MedicationUrgencyProgressSection
                v-if="stockProgressPercent !== null"
                :tone="stockProgressTone"
                :progress-percent="stockProgressPercent"
                :status-line="supplyEstimateLine"
                :progress-aria-label="stockProgressAriaLabel"
                :critical-alert-label="t('patient.inventory.lowStockBadge')"
                :warning-alert-label="t('patient.inventory.warningStockIconAria')"
            />

            <p
                v-else
                class="text-base font-semibold leading-relaxed text-text-heading sm:text-lg"
            >
                {{ supplyEstimateLine }}
            </p>

            <div class="flex w-full min-w-0 justify-start">
                <div
                    class="min-w-0 flex-1"
                    :class="currentStockPanelClass"
                >
                    <div :class="currentStockIconWrapClass">
                        <Layers
                            class="size-5 sm:size-6"
                            aria-hidden="true"
                        />
                    </div>
                    <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                        <span
                            class="text-sm font-semibold leading-snug text-text-heading sm:text-base"
                        >
                            {{ t('patient.medications.fields.currentStock') }}
                        </span>
                        <span
                            class="whitespace-pre-wrap wrap-break-word text-2xl font-bold tabular-nums leading-none tracking-tight text-text-heading sm:text-3xl"
                        >
                            {{ currentStockDisplayLine }}
                        </span>
                    </div>
                </div>
            </div>

            <div
                v-if="props.canAdjustStock"
                class="flex w-full min-w-0 justify-center pt-1"
            >
                <div class="mx-auto w-full max-w-xs">
                    <Button
                        type="button"
                        variant="outline"
                        size="lg"
                        :class="[
                            'flex min-h-12 w-full touch-manipulation flex-row flex-nowrap items-center justify-center gap-2 px-4 text-base font-bold sm:min-h-14 sm:gap-3 sm:text-lg',
                            adjustStockButtonClass,
                        ]"
                        @click="stockEditOpen = true"
                    >
                        <PackagePlus
                            class="size-5 shrink-0 sm:size-6"
                            aria-hidden="true"
                        />
                        <span class="min-w-0 leading-snug">
                            {{ t('patient.inventory.adjustStock') }}
                        </span>
                    </Button>
                </div>
            </div>
        </template>

        <p
            v-else
            class="text-base leading-relaxed text-text-muted sm:text-lg"
        >
            {{ t('patient.inventory.stockMissing') }}
        </p>

        <MedicationInventoryStockEditDialog
            v-if="primaryStock !== undefined && props.canAdjustStock"
            v-model:open="stockEditOpen"
            :medication-id="medication.id"
            :medication-name="medication.name"
            :dose-unit="stockDisplayDoseUnit"
            :stock="primaryStock"
            :stock-progress-tone="stockProgressTone"
            :stock-pieces-per-package="props.medication.stock_pieces_per_package"
            :form-id="stockFormId"
            :id-prefix="resolvedIdPrefix"
            :dialog-content-class="dialogContentClass"
            :update-route-name="props.updateRouteName"
        />
    </div>
</template>
