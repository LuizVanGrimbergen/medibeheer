<script setup lang="ts">
import { AlertTriangle, Layers, PackagePlus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationInventoryStockEditDialog from '@/Components/Patient/Inventory/form/MedicationInventoryStockEditDialog.vue';
import { Button } from '@/Components/ui/button';
import { Progress } from '@/Components/ui/progress';
import {
    medicationStockCurrentStockIconWrapClass,
    medicationStockCurrentStockPanelClass,
} from '@/lib/patient/inventory/medicationStockCurrentStockPanelClasses';
import { medicationListVisualTone } from '@/lib/patient/inventory/medicationListVisualTone';
import { medicationStockProgressPercent } from '@/lib/patient/inventory/medicationStockProgressPercent';
import { patientShellDialogContentClass } from '@/lib/patient/patientShellDialogLayout';
import { parseMedicationStrengthFromStored } from '@/lib/patient/medications/strength/parseMedicationStrengthFromStored';
import { formatMedicationStockDisplayAmount } from '@/lib/patient/medications/stock/formatMedicationStockDisplayAmount';
import { medicationStockDisplayDoseUnit } from '@/lib/patient/medications/stock/medicationStockDisplayDoseUnit';
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

const showSupplyAlertRow = computed((): boolean => {
    const tone = stockProgressTone.value;

    return tone === 'critical' || tone === 'warning';
});

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

const stockProgressTone = computed(() => medicationListVisualTone(props.medication));

const stockProgressIndicatorClass = computed((): string | undefined => {
    const tone = stockProgressTone.value;

    if (tone === 'critical') {
        return 'bg-danger';
    }

    if (tone === 'warning') {
        return 'bg-stock-near dark:bg-stock-near-dark';
    }

    if (tone === 'safe') {
        return 'bg-success';
    }

    return undefined;
});

const adjustStockButtonClass = computed((): string => {
    const tone = stockProgressTone.value;

    const base =
        'rounded-3xl border-2 bg-surface text-text-heading hover:bg-surface-hover';

    if (tone === 'critical') {
        return `${base} border-danger/70 hover:bg-danger/[0.06] dark:border-danger/80 dark:hover:bg-danger/[0.1] [&_svg]:text-danger`;
    }

    if (tone === 'warning') {
        return `${base} border-stock-near/70 hover:bg-stock-near/[0.06] dark:border-stock-near-dark/75 dark:hover:bg-stock-near-dark/[0.1] [&_svg]:text-stock-near dark:[&_svg]:text-stock-near-dark`;
    }

    if (tone === 'safe') {
        return `${base} border-success/55 hover:bg-success/[0.06] dark:border-success/65 dark:hover:bg-success/[0.1] [&_svg]:text-success`;
    }

    return `${base} border-border/80 [&_svg]:text-text-heading`;
});

const currentStockPanelClass = computed((): string =>
    medicationStockCurrentStockPanelClass(stockProgressTone.value),
);

const currentStockIconWrapClass = computed((): string =>
    medicationStockCurrentStockIconWrapClass(stockProgressTone.value),
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

const supplyEstimateLineClass = computed((): string => {
    const tone = stockProgressTone.value;

    if (tone === 'critical') {
        return 'text-danger';
    }

    if (tone === 'warning') {
        return 'text-stock-near dark:text-stock-near-dark';
    }

    if (tone === 'safe') {
        return 'text-success';
    }

    return 'text-text-heading';
});

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
            <Progress
                v-if="stockProgressPercent !== null"
                :model-value="stockProgressPercent"
                :indicator-class="stockProgressIndicatorClass"
                :aria-label="
                    t('patient.inventory.stockProgressAria', {
                        days: String(props.medication.supply_estimate_days ?? 0),
                    })
                "
                class="h-4 w-full sm:h-5"
            />

            <div
                class="flex min-w-0 items-start gap-3 sm:items-center sm:gap-3"
                :role="showSupplyAlertRow ? 'alert' : undefined"
            >
                <template v-if="showSupplyAlertRow">
                    <span class="sr-only">{{
                        stockProgressTone === 'critical'
                            ? t('patient.inventory.lowStockBadge')
                            : t('patient.inventory.warningStockIconAria')
                    }}</span>
                    <AlertTriangle
                        class="mt-0.5 size-6 shrink-0 sm:mt-0 sm:size-7"
                        :class="
                            stockProgressTone === 'critical'
                                ? 'text-danger'
                                : 'text-stock-near dark:text-stock-near-dark'
                        "
                        aria-hidden="true"
                    />
                </template>
                <p
                    class="min-w-0 text-base font-semibold leading-relaxed sm:text-lg"
                    :class="supplyEstimateLineClass"
                >
                    {{ supplyEstimateLine }}
                </p>
            </div>

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
            :dose-unit="stockDisplayDoseUnit"
            :stock="primaryStock"
            :form-id="stockFormId"
            :id-prefix="resolvedIdPrefix"
            :dialog-content-class="dialogContentClass"
            :update-route-name="props.updateRouteName"
        />
    </div>
</template>
