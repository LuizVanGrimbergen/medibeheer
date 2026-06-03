<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Images, Loader2, Package, PillBottle } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import InventoryVacationDateField from '@/Components/Patient/Inventory/InventoryVacationDateField.vue';
import InventoryVacationMetricBox from '@/Components/Patient/Inventory/InventoryVacationMetricBox.vue';
import InventoryVacationPickupBoxCalculator from '@/Components/Patient/Inventory/InventoryVacationPickupBoxCalculator.vue';
import InventoryVacationShareStepPanel from '@/Components/Patient/Inventory/InventoryVacationShareStepPanel.vue';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { useInventoryVacationShareToPhotos } from '@/composables/useInventoryVacationShareToPhotos';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import {
    patientAppointmentFormPrimaryPairButtonClass,
    patientSoftDangerActionButtonClass,
} from '@/lib/patient/appointments/ui/patientSoftDangerActionButtonClass';
import { localCalendarDateIsoToday } from '@/lib/patient/appointments/validation/appointmentStartsAtLocalValidation';
import { buildInventoryVacationShareImagePayload } from '@/lib/patient/inventory/buildInventoryVacationShareImagePayload';
import { formatInventoryVacationDateLabel } from '@/lib/patient/inventory/formatInventoryVacationDateLabel';
import {
    inventoryVacationMetricGridClass,
    inventoryVacationResultsCardClass,
} from '@/lib/patient/inventory/inventoryVacationUiClasses';
import type { PatientInventoryVacationPageProps } from '@/lib/patient/inventory/patientInventoryVacationPageProps';
import { medicationDoseUnitChipForAmount } from '@/lib/patient/medications/options/medicationDoseUnitChipForAmount';
import { medicationStockDisplayDoseUnit } from '@/lib/patient/medications/stock/medicationStockDisplayDoseUnit';
import {
    patientPageIntroClass,
    patientPageSectionTitleClass,
    patientPageTitleClass,
} from '@/lib/patient/patientPageTypography';
import type { MedicationDoseUnitValue } from '@/lib/types';
import { MEDICATION_DOSE_UNIT_VALUES } from '@/lib/types';
const props = defineProps<PatientInventoryVacationPageProps>();

const { t } = useI18n();

const form = useForm({
    starts_on: props.starts_on,
    ends_on: props.ends_on,
});

const minDateIso = computed(() => localCalendarDateIsoToday());

const showResults = computed(() => props.result !== null);

const formattedStartsOn = computed(() =>
    formatInventoryVacationDateLabel(form.starts_on),
);

const formattedEndsOn = computed(() =>
    formatInventoryVacationDateLabel(form.ends_on),
);

const pickupItemsWithSavedPackage = computed(() => {
    if (props.result === null) {
        return [];
    }

    return props.result.items.filter(
        (item) =>
            item.stock_pieces_per_package !== null &&
            item.stock_pieces_per_package > 0,
    );
});

const vacationSavedPackageHint = computed((): string | null => {
    const items = pickupItemsWithSavedPackage.value;

    if (items.length === 0) {
        return null;
    }

    const allLiquid = items.every((item) => item.type_medication === 'liquid');

    if (allLiquid) {
        return t(
            'patient.inventory.vacationPickupCalculator.liquid.savedPiecesHint',
        );
    }

    return t('patient.inventory.vacationPickupCalculator.savedPiecesHint');
});

const vacationSavedPackageHintUsesLiquidIcon = computed(
    (): boolean =>
        pickupItemsWithSavedPackage.value.length > 0 &&
        pickupItemsWithSavedPackage.value.every(
            (item) => item.type_medication === 'liquid',
        ),
);

const vacationDaysLabel = computed((): string => {
    const days = props.result?.vacation_days;

    if (days === undefined) {
        return '';
    }

    if (days === 1) {
        return t('patient.inventory.vacationResultsDaysValueOne');
    }

    return t('patient.inventory.vacationResultsDaysValue', {
        days: String(days),
    });
});

function submit(): void {
    form.post(route('patient.inventory.vacation.store'), {
        preserveScroll: true,
    });
}

function doseUnitForItem(doseUnit: string | null): string | null {
    return medicationStockDisplayDoseUnit(doseUnit, null);
}

function doseUnitChipForItem(
    amount: string,
    doseUnit: string | null,
): string | null {
    if (doseUnit === null || doseUnit === '') {
        return null;
    }

    if (
        !(MEDICATION_DOSE_UNIT_VALUES as readonly string[]).includes(doseUnit)
    ) {
        return null;
    }

    const chip = medicationDoseUnitChipForAmount(
        t,
        amount,
        doseUnit as MedicationDoseUnitValue,
    );

    if (chip === '—') {
        return null;
    }

    return chip;
}

const vacationShareImagePayload = computed(() => {
    if (props.result === null) {
        return null;
    }

    const appName = import.meta.env.VITE_APP_NAME ?? 'Medibeheer';

    return buildInventoryVacationShareImagePayload({
        result: props.result,
        startsOn: form.starts_on,
        endsOn: form.ends_on,
        vacationDaysLabel: vacationDaysLabel.value,
        title: t('patient.inventory.vacationDialogTitle'),
        periodHeading: t('patient.inventory.vacationResultsPeriodHeading'),
        departureLabel: t('patient.inventory.vacationResultsDepartureLabel'),
        returnLabel: t('patient.inventory.vacationResultsReturnLabel'),
        savedPackageHint: vacationSavedPackageHint.value,
        totalLabel: t('patient.inventory.vacationPickupCalculator.totalLabel'),
        minimumBoxesLabel: t(
            'patient.inventory.vacationPickupCalculator.minimumBoxesLabel',
        ),
        liquidMinimumBoxesLabel: t(
            'patient.medications.stockCalculator.liquid.numberOfBottles',
        ),
        piecesPerBoxLabel: t(
            'patient.medications.stockCalculator.piecesPerBox',
        ),
        liquidPiecesPerBoxLabel: t(
            'patient.medications.stockCalculator.liquid.mlPerBottle',
        ),
        listHeading: t('patient.inventory.vacationResultsTitle'),
        emptyMessage: t('patient.inventory.vacationEmptyResults'),
        skippedNote:
            props.result.skipped_medication_count > 0
                ? t('patient.inventory.vacationSkippedNote', {
                      count: String(props.result.skipped_medication_count),
                  })
                : null,
        footerLabel: appName,
        doseUnitForItem,
        doseUnitChipForItem,
    });
});

const {
    isSaving,
    saveError,
    saveShareHintVisible,
    savedShareImageCount,
    shareFlowOpen,
    shareStepCurrent,
    shareStepTotal,
    prepareShareFiles,
    shareCurrentImage,
} = useInventoryVacationShareToPhotos({
    shareImagePayload: vacationShareImagePayload,
    startsOn: computed(() => form.starts_on),
    t,
});
</script>

<template>
    <Head>
        <title>{{ t('patient.inventory.vacationDialogTitle') }}</title>
    </Head>

    <PatientLayout>
        <PatientPageShell :title="t('patient.inventory.vacationDialogTitle')">
            <div class="space-y-3">
                <h1 :class="patientPageTitleClass">
                    {{ t('patient.inventory.vacationDialogTitle') }}
                </h1>
                <p
                    v-if="!showResults"
                    :class="[patientPageIntroClass, 'max-w-prose']"
                >
                    {{ t('patient.inventory.vacationDialogDescription') }}
                </p>
            </div>

            <form
                v-if="!showResults"
                class="space-y-5"
                novalidate
                @submit.prevent="submit"
            >
                <Card
                    class="border-border/80 bg-surface text-text rounded-2xl border shadow-md shadow-black/[0.04] sm:rounded-3xl"
                >
                    <CardContent class="space-y-6 p-5 sm:p-6 md:p-7">
                        <InventoryVacationDateField
                            id="patient-inventory-vacation-starts-on"
                            v-model="form.starts_on"
                            :label="
                                t('patient.inventory.vacationStartsOnLabel')
                            "
                            :min="minDateIso"
                            :error="form.errors.starts_on"
                        />
                        <InventoryVacationDateField
                            id="patient-inventory-vacation-ends-on"
                            v-model="form.ends_on"
                            :label="t('patient.inventory.vacationEndsOnLabel')"
                            :min="form.starts_on || minDateIso"
                            :error="form.errors.ends_on"
                        />
                    </CardContent>
                </Card>

                <div
                    class="border-border/80 bg-surface flex w-full min-w-0 flex-col gap-3 rounded-3xl border-2 p-5 shadow-sm shadow-black/[0.04] sm:flex-row-reverse sm:gap-3 sm:p-6"
                >
                    <Button
                        type="submit"
                        variant="default"
                        size="lg"
                        :class="patientAppointmentFormPrimaryPairButtonClass"
                        :disabled="form.processing"
                    >
                        {{
                            form.processing
                                ? t('patient.inventory.vacationLoading')
                                : t('patient.inventory.vacationCalculate')
                        }}
                    </Button>
                    <Button
                        as-child
                        variant="secondary"
                        size="lg"
                        :class="patientSoftDangerActionButtonClass"
                    >
                        <Link :href="route('patient.inventory')">
                            {{ t('patient.medications.actions.cancel') }}
                        </Link>
                    </Button>
                </div>
            </form>

            <div v-else-if="props.result !== null" class="space-y-5">
                <Card :class="inventoryVacationResultsCardClass">
                    <CardContent class="space-y-3 p-4 sm:p-5">
                        <div
                            class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1"
                        >
                            <h2 :class="patientPageSectionTitleClass">
                                {{
                                    t(
                                        'patient.inventory.vacationResultsPeriodHeading',
                                    )
                                }}
                            </h2>
                            <p
                                class="text-text-heading text-xl leading-none font-bold tabular-nums"
                            >
                                {{ vacationDaysLabel }}
                            </p>
                        </div>

                        <dl :class="inventoryVacationMetricGridClass">
                            <InventoryVacationMetricBox
                                :label="
                                    t(
                                        'patient.inventory.vacationResultsDepartureLabel',
                                    )
                                "
                                :value="formattedStartsOn"
                            />
                            <InventoryVacationMetricBox
                                :label="
                                    t(
                                        'patient.inventory.vacationResultsReturnLabel',
                                    )
                                "
                                :value="formattedEndsOn"
                            />
                        </dl>
                    </CardContent>
                </Card>

                <p
                    v-if="props.result.items.length === 0"
                    class="border-border/80 bg-surface-2/80 text-text rounded-2xl border px-4 py-5 text-base leading-relaxed md:text-lg"
                >
                    {{ t('patient.inventory.vacationEmptyResults') }}
                </p>

                <h2
                    v-if="props.result.items.length > 0"
                    :class="patientPageSectionTitleClass"
                >
                    {{ t('patient.inventory.vacationResultsTitle') }}
                </h2>

                <div class="space-y-3">
                    <InventoryVacationShareStepPanel
                        v-if="shareFlowOpen"
                        :step-current="shareStepCurrent"
                        :step-total="shareStepTotal"
                        :steps-completed="shareStepCurrent - 1"
                        @share="shareCurrentImage"
                    />

                    <Button
                        v-else
                        type="button"
                        variant="default"
                        size="lg"
                        :class="[
                            patientAppointmentFormPrimaryPairButtonClass,
                            'w-full',
                        ]"
                        :disabled="isSaving"
                        :aria-label="
                            t('patient.inventory.vacationSaveToPhotos')
                        "
                        @click="prepareShareFiles"
                    >
                        <Loader2
                            v-if="isSaving"
                            class="size-6 shrink-0 animate-spin"
                            aria-hidden="true"
                        />
                        <Images
                            v-else
                            class="size-6 shrink-0"
                            aria-hidden="true"
                        />
                        <span>
                            {{
                                isSaving
                                    ? t('patient.inventory.vacationSaving')
                                    : t(
                                          'patient.inventory.vacationSaveToPhotos',
                                      )
                            }}
                        </span>
                    </Button>

                    <p
                        v-if="saveError !== null"
                        class="text-destructive text-sm leading-relaxed sm:text-base"
                        role="alert"
                    >
                        {{ saveError }}
                    </p>

                    <p
                        v-else-if="saveShareHintVisible"
                        class="text-text-muted text-sm leading-relaxed sm:text-base"
                    >
                        {{
                            savedShareImageCount > 1
                                ? t(
                                      'patient.inventory.vacationSaveShareHintMultiple',
                                      {
                                          count: String(savedShareImageCount),
                                      },
                                  )
                                : t('patient.inventory.vacationSaveShareHint')
                        }}
                    </p>
                </div>

                <div
                    v-if="vacationSavedPackageHint !== null"
                    class="border-border/60 bg-surface-2/30 dark:border-border/70 dark:bg-surface-2/50 flex w-full min-w-0 items-start gap-3.5 rounded-2xl border px-4 py-3.5 sm:gap-4 sm:rounded-3xl sm:px-5 sm:py-4"
                >
                    <div
                        class="bg-primary/12 text-primary flex size-11 shrink-0 items-center justify-center rounded-xl sm:size-14 sm:rounded-2xl"
                    >
                        <PillBottle
                            v-if="vacationSavedPackageHintUsesLiquidIcon"
                            class="size-5 sm:size-6"
                            aria-hidden="true"
                        />
                        <Package
                            v-else
                            class="size-5 sm:size-6"
                            aria-hidden="true"
                        />
                    </div>
                    <div class="flex min-w-0 flex-1 flex-col gap-1">
                        <span
                            class="text-text-heading text-sm leading-snug font-semibold sm:text-base"
                        >
                            {{ vacationSavedPackageHint }}
                        </span>
                    </div>
                </div>

                <ul
                    v-if="props.result.items.length > 0"
                    class="flex flex-col gap-4"
                >
                    <li
                        v-for="item in props.result.items"
                        :key="item.medication_id"
                    >
                        <Card :class="inventoryVacationResultsCardClass">
                            <CardContent class="space-y-4 p-5 sm:p-6">
                                <p
                                    class="text-text-heading text-lg leading-snug font-bold"
                                >
                                    {{ item.name }}
                                </p>
                                <InventoryVacationPickupBoxCalculator
                                    :id-prefix="`vacation-pickup-${item.medication_id}`"
                                    :dose-unit="doseUnitForItem(item.dose_unit)"
                                    :medication-type="item.type_medication"
                                    :pickup-quantity="item.pickup_quantity"
                                    :stock-pieces-per-package="
                                        item.stock_pieces_per_package
                                    "
                                />
                            </CardContent>
                        </Card>
                    </li>
                </ul>

                <p
                    v-if="props.result.skipped_medication_count > 0"
                    class="text-text-muted text-sm leading-relaxed sm:text-base"
                >
                    {{
                        t('patient.inventory.vacationSkippedNote', {
                            count: String(
                                props.result.skipped_medication_count,
                            ),
                        })
                    }}
                </p>

                <div
                    class="border-border/80 bg-surface rounded-3xl border-2 p-5 shadow-sm shadow-black/[0.04] sm:p-6"
                >
                    <Button
                        as-child
                        variant="default"
                        size="lg"
                        :class="patientAppointmentFormPrimaryPairButtonClass"
                    >
                        <Link :href="route('patient.inventory')">
                            {{ t('patient.inventory.vacationDone') }}
                        </Link>
                    </Button>
                </div>
            </div>
        </PatientPageShell>
    </PatientLayout>
</template>
