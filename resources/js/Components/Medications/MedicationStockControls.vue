<script setup lang="ts">
import { PackagePlus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationCurrentStockPanel from '@/Components/Medications/MedicationCurrentStockPanel.vue';
import MedicationUrgencyProgressSection from '@/Components/Medications/MedicationUrgencyProgressSection.vue';
import MedicationInventoryStockEditDialog from '@/Components/Patient/Inventory/form/MedicationInventoryStockEditDialog.vue';
import { Button } from '@/Components/ui/button';
import { medicationListVisualTone } from '@/lib/patient/inventory/medicationListVisualTone';
import { medicationSupplyEstimateLine } from '@/lib/patient/inventory/medicationSupplyEstimateLine';
import { medicationStockProgressPercent } from '@/lib/patient/inventory/medicationStockProgressPercent';
import { medicationStockDisplayDoseUnit } from '@/lib/patient/medications/stock/medicationStockDisplayDoseUnit';
import { parseMedicationStrengthFromStored } from '@/lib/patient/medications/strength/parseMedicationStrengthFromStored';
import { medicationUrgencyOutlineButtonClass } from '@/lib/patient/medications/urgency/medicationUrgencyPanelClasses';
import { patientShellDialogContentClass } from '@/lib/patient/patientShellDialogLayout';
import type { MedicationListItem } from '@/lib/types';

const props = withDefaults(
    defineProps<{
        medication: MedicationListItem;
        updateRouteName?: string;
        idPrefix?: string;
        canAdjustStock?: boolean;
        showSummary?: boolean;
        showUrgencySummary?: boolean;
    }>(),
    {
        updateRouteName: 'patient.medications.stocks.update',
        canAdjustStock: true,
        showSummary: true,
        showUrgencySummary: true,
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

const stockProgressTone = computed(() =>
    medicationListVisualTone(props.medication),
);

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

const supplyEstimateLine = computed((): string =>
    medicationSupplyEstimateLine(t, props.medication),
);

const stockProgressAriaLabel = computed((): string =>
    t('patient.inventory.stockProgressAria', {
        days: String(props.medication.supply_estimate_days ?? 0),
    }),
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
</script>

<template>
    <div class="space-y-3.5">
        <template v-if="primaryStock !== undefined">
            <template v-if="props.showSummary">
                <template v-if="props.showUrgencySummary">
                    <MedicationUrgencyProgressSection
                        v-if="stockProgressPercent !== null"
                        :tone="stockProgressTone"
                        :progress-percent="stockProgressPercent"
                        :status-line="supplyEstimateLine"
                        :progress-aria-label="stockProgressAriaLabel"
                        :critical-alert-label="
                            t('patient.inventory.lowStockBadge')
                        "
                        :warning-alert-label="
                            t('patient.inventory.warningStockIconAria')
                        "
                    />

                    <p
                        v-else
                        class="text-text-heading text-base leading-relaxed font-semibold sm:text-lg"
                    >
                        {{ supplyEstimateLine }}
                    </p>
                </template>

                <MedicationCurrentStockPanel :medication="medication" />
            </template>

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

        <p v-else class="text-text-muted text-base leading-relaxed sm:text-lg">
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
            :stock-pieces-per-package="
                props.medication.stock_pieces_per_package
            "
            :form-id="stockFormId"
            :id-prefix="resolvedIdPrefix"
            :dialog-content-class="dialogContentClass"
            :update-route-name="props.updateRouteName"
        />
    </div>
</template>
