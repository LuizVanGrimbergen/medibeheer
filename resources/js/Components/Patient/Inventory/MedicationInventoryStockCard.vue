<script setup lang="ts">
import { AlertTriangle, Layers, PackagePlus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationTypeLeadIcon from '@/Components/Medications/MedicationTypeLeadIcon.vue';
import MedicationInventoryStockEditDialog from '@/Components/Patient/Inventory/MedicationInventoryStockEditDialog.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Progress } from '@/Components/ui/progress';
import { medicationListVisualTone } from '@/lib/patient/inventory/medicationListVisualTone';
import { medicationListVisualToneClasses } from '@/lib/patient/inventory/medicationListVisualToneClasses';
import { medicationStockProgressPercent } from '@/lib/patient/inventory/medicationStockProgressPercent';
import { patientShellDialogContentClass } from '@/lib/patient/patientShellDialogLayout';
import { formatMedicationStockDisplayAmount } from '@/lib/patient/medications/stock/formatMedicationStockDisplayAmount';
import type { MedicationListItem, MedicationTypeValue } from '@/lib/types';

const props = defineProps<{
    medication: MedicationListItem;
}>();

const { t } = useI18n();

const stockEditOpen = ref(false);

const dialogContentClass = patientShellDialogContentClass('md');

const primaryStock = computed(() => props.medication.stocks[0]);

const stockFormId = computed(
    () => `patient-inventory-stock-form-${props.medication.id}-${primaryStock.value?.id ?? 0}`,
);

const stockIdPrefix = computed(
    () => `patient-inventory-stock-${props.medication.id}-${primaryStock.value?.id ?? 0}`,
);

const typeLabel = computed(() =>
    t(`patient.medications.types.${props.medication.type_medication as MedicationTypeValue}`),
);

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
        stock.current_stock,
        stock.low_stock,
        props.medication.dose_unit,
    );
});

const stockProgressTone = computed(() => medicationListVisualTone(props.medication));

const inventoryVisualToneClasses = computed(() =>
    medicationListVisualToneClasses(stockProgressTone.value),
);

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

const currentStockPanelClass = computed((): string => {
    const tone = stockProgressTone.value;
    const base =
        'flex min-w-0 w-full items-start gap-3.5 rounded-2xl border px-4 py-3.5 sm:gap-4 sm:rounded-3xl sm:px-5 sm:py-4';

    if (tone === 'critical') {
        return `${base} border-danger/25 bg-danger/[0.07] dark:border-danger/35 dark:bg-danger/[0.1]`;
    }

    if (tone === 'warning') {
        return `${base} border-stock-near/25 bg-stock-near/[0.07] dark:border-stock-near-dark/35 dark:bg-stock-near-dark/[0.1]`;
    }

    if (tone === 'safe') {
        return `${base} border-success/25 bg-success/[0.06] dark:border-success/35 dark:bg-success/[0.09]`;
    }

    return `${base} border-border/60 bg-surface-2/90 dark:border-border/70 dark:bg-surface-2`;
});

const currentStockIconWrapClass = computed((): string => {
    const tone = stockProgressTone.value;
    const base =
        'flex size-11 shrink-0 items-center justify-center rounded-xl sm:size-14 sm:rounded-2xl';

    if (tone === 'critical') {
        return `${base} bg-danger/15 text-danger dark:bg-danger/20`;
    }

    if (tone === 'warning') {
        return `${base} bg-stock-near/15 text-stock-near dark:bg-stock-near-dark/20 dark:text-stock-near-dark`;
    }

    if (tone === 'safe') {
        return `${base} bg-success/15 text-success dark:bg-success/20`;
    }

    return `${base} bg-primary/12 text-primary`;
});

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

const currentStockDisplayLine = computed((): string =>
    formatMedicationStockDisplayAmount(
        t,
        primaryStockAmountTrimmed.value,
        props.medication.dose_unit,
    ),
);
</script>

<template>
    <Card
        class="min-w-0 w-full rounded-3xl border-2 bg-surface text-text shadow-md shadow-black/[0.04]"
        :class="inventoryVisualToneClasses.border"
    >
        <CardContent class="relative p-4 pb-6 pt-5 sm:p-8">
            <div
                class="flex min-w-0 w-full flex-col gap-5 sm:flex-row sm:items-start sm:gap-6"
            >
                <div
                    class="flex size-12 shrink-0 items-center justify-center rounded-2xl sm:size-16"
                    :class="inventoryVisualToneClasses.pillWrap"
                >
                    <span class="sr-only">{{ typeLabel }}</span>
                    <MedicationTypeLeadIcon
                        :medication-type="medication.type_medication"
                        :icon-tone-class="inventoryVisualToneClasses.pillIcon"
                    />
                </div>
                <div
                    class="flex w-full min-w-0 flex-col space-y-3.5 sm:flex-1 sm:items-stretch"
                >
                    <p
                        class="min-w-0 wrap-break-word text-lg font-bold leading-snug text-text-heading sm:text-xl"
                    >
                        {{ medication.name }}
                    </p>

                    <template v-if="primaryStock !== undefined">
                        <Progress
                            v-if="stockProgressPercent !== null"
                            :model-value="stockProgressPercent"
                            :indicator-class="stockProgressIndicatorClass"
                            :aria-label="
                                t('patient.inventory.stockProgressAria', {
                                    current: primaryStock.current_stock.trim(),
                                    low: primaryStock.low_stock.trim(),
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
                                class="min-w-0 text-lg font-semibold leading-relaxed sm:text-xl"
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
                                        class="text-sm font-semibold leading-snug text-text-muted sm:text-base"
                                    >
                                        {{ t('patient.medications.fields.currentStock') }}
                                    </span>
                                    <span
                                        class="whitespace-pre-wrap wrap-break-word text-3xl font-bold tabular-nums leading-none tracking-tight text-text-heading sm:text-4xl"
                                    >
                                        {{ currentStockDisplayLine }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex w-full min-w-0 justify-center pt-1 sm:pt-2"
                        >
                            <div class="mx-auto w-full max-w-xs">
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="lg"
                                    :class="[
                                        'flex min-h-14 w-full touch-manipulation flex-row flex-nowrap items-center justify-center gap-2 px-4 text-base font-bold sm:min-h-16 sm:gap-3 sm:px-5 sm:text-xl',
                                        adjustStockButtonClass,
                                    ]"
                                    @click="stockEditOpen = true"
                                >
                                    <PackagePlus
                                        class="size-5 shrink-0 sm:size-7"
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
                        class="text-lg leading-relaxed text-text sm:text-xl"
                    >
                        {{ t('patient.inventory.stockMissing') }}
                    </p>
                </div>
            </div>
        </CardContent>
    </Card>

    <MedicationInventoryStockEditDialog
        v-if="primaryStock !== undefined"
        v-model:open="stockEditOpen"
        :medication-id="medication.id"
        :dose-unit="medication.dose_unit"
        :stock="primaryStock"
        :form-id="stockFormId"
        :id-prefix="stockIdPrefix"
        :dialog-content-class="dialogContentClass"
    />
</template>
