<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { Images, Loader2, Package, PillBottle } from 'lucide-vue-next';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientShellPageWizard from '@/Components/Patient/form/PatientShellPageWizard.vue';
import PatientShellWizardScrollBody from '@/Components/Patient/form/PatientShellWizardScrollBody.vue';
import InventoryVacationDateField from '@/Components/Patient/Inventory/InventoryVacationDateField.vue';
import InventoryVacationExpiringPrescriptionsSection from '@/Components/Patient/Inventory/InventoryVacationExpiringPrescriptionsSection.vue';
import InventoryVacationMetricBox from '@/Components/Patient/Inventory/InventoryVacationMetricBox.vue';
import InventoryVacationPickupBoxCalculator from '@/Components/Patient/Inventory/InventoryVacationPickupBoxCalculator.vue';
import InventoryVacationShareStepPanel from '@/Components/Patient/Inventory/InventoryVacationShareStepPanel.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { useInventoryVacationShareToPhotos } from '@/composables/inventory/useInventoryVacationShareToPhotos';
import PatientLayout from '@/Layouts/PatientLayout.vue';
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
import { patientPageSectionTitleClass } from '@/lib/patient/patientPageTypography';
import {
    patientFormWizardFooterCancelButtonClass,
    patientFormWizardFooterPrimaryButtonClass,
    patientFormWizardFooterRowClass,
    patientShellPageFillClass,
    patientShellWizardCardClass,
    patientShellWizardCardInnerClass,
    patientShellWizardFormClass,
    patientShellWizardStepPanelClass,
} from '@/lib/patient/patientShellDialogLayout';
import type { MedicationDoseUnitValue } from '@/lib/types';
import { MEDICATION_DOSE_UNIT_VALUES } from '@/lib/types';
const props = defineProps<PatientInventoryVacationPageProps>();

const page = usePage();
const { t } = useI18n();

onMounted(() => {
    if (props.result === null) {
        return;
    }

    if (Object.hasOwn(page.props, 'expiring_prescriptions')) {
        return;
    }

    router.post(
        route('patient.inventory.vacation.store'),
        {
            starts_on: props.starts_on,
            ends_on: props.ends_on,
        },
        {
            preserveScroll: true,
            preserveState: true,
            only: ['expiring_prescriptions'],
        },
    );
});

const form = useForm({
    starts_on: props.starts_on,
    ends_on: props.ends_on,
});

const minDateIso = computed(() => localCalendarDateIsoToday());

const showResults = computed(() => props.result !== null);

const pageIntro = computed(() =>
    showResults.value ? '' : t('patient.inventory.vacationDialogDescription'),
);

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
        expiringPrescriptions: props.expiring_prescriptions,
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
    plannedShareImageCount,
    prepareShareFiles,
    shareCurrentImage,
} = useInventoryVacationShareToPhotos({
    shareImagePayload: vacationShareImagePayload,
    startsOn: computed(() => form.starts_on),
    t,
});

const shareStepPanelRef = ref<HTMLElement | null>(null);

watch(shareFlowOpen, async (open) => {
    if (!open) {
        return;
    }

    await nextTick();
    shareStepPanelRef.value?.scrollIntoView({
        behavior: 'smooth',
        block: 'nearest',
    });
});
</script>

<template>
    <Head>
        <title>{{ t('patient.inventory.vacationDialogTitle') }}</title>
        <meta
            name="description"
            :content="t('patient.inventory.vacationMetaDescription')"
        />
    </Head>

    <PatientLayout>
        <div :class="patientShellPageFillClass">
            <PatientShellPageWizard
                :title="t('patient.inventory.vacationDialogTitle')"
                :description="pageIntro"
            >
                <form
                    v-if="!showResults"
                    :class="patientShellWizardFormClass"
                    novalidate
                    @submit.prevent="submit"
                >
                    <PatientShellWizardScrollBody :active="true">
                        <div :class="patientShellWizardStepPanelClass">
                            <Card :class="patientShellWizardCardClass">
                                <CardContent class="p-0">
                                    <div
                                        :class="[
                                            patientShellWizardCardInnerClass,
                                            'space-y-6',
                                        ]"
                                    >
                                        <InventoryVacationDateField
                                            id="patient-inventory-vacation-starts-on"
                                            v-model="form.starts_on"
                                            :label="
                                                t(
                                                    'patient.inventory.vacationStartsOnLabel',
                                                )
                                            "
                                            :min="minDateIso"
                                            :error="form.errors.starts_on"
                                        />
                                        <InventoryVacationDateField
                                            id="patient-inventory-vacation-ends-on"
                                            v-model="form.ends_on"
                                            :label="
                                                t(
                                                    'patient.inventory.vacationEndsOnLabel',
                                                )
                                            "
                                            :min="form.starts_on || minDateIso"
                                            :error="form.errors.ends_on"
                                        />
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        <template #footer>
                            <div :class="patientFormWizardFooterRowClass">
                                <Button
                                    as-child
                                    variant="secondary"
                                    size="lg"
                                    :class="
                                        patientFormWizardFooterCancelButtonClass
                                    "
                                    :disabled="form.processing"
                                >
                                    <Link :href="route('patient.inventory')">
                                        {{
                                            t(
                                                'patient.inventory.vacationBackToInventory',
                                            )
                                        }}
                                    </Link>
                                </Button>
                                <Button
                                    type="submit"
                                    variant="default"
                                    size="lg"
                                    :class="
                                        patientFormWizardFooterPrimaryButtonClass
                                    "
                                    :disabled="form.processing"
                                >
                                    {{
                                        form.processing
                                            ? t(
                                                  'patient.inventory.vacationLoading',
                                              )
                                            : t(
                                                  'patient.inventory.vacationCalculate',
                                              )
                                    }}
                                </Button>
                            </div>
                        </template>
                    </PatientShellWizardScrollBody>
                </form>

                <div
                    v-else-if="props.result !== null"
                    :class="patientShellWizardFormClass"
                >
                    <PatientShellWizardScrollBody :active="true">
                        <div :class="patientShellWizardStepPanelClass">
                            <Card :class="inventoryVacationResultsCardClass">
                                <CardContent class="space-y-3 p-4 sm:p-5">
                                    <div
                                        class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1"
                                    >
                                        <h2
                                            :class="
                                                patientPageSectionTitleClass
                                            "
                                        >
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

                                    <dl
                                        :class="
                                            inventoryVacationMetricGridClass
                                        "
                                    >
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
                                {{
                                    t('patient.inventory.vacationEmptyResults')
                                }}
                            </p>

                            <div class="space-y-3">
                                <div
                                    v-if="shareFlowOpen"
                                    ref="shareStepPanelRef"
                                >
                                    <InventoryVacationShareStepPanel
                                        :step-current="shareStepCurrent"
                                        :step-total="shareStepTotal"
                                        :steps-completed="shareStepCurrent - 1"
                                        @share="shareCurrentImage"
                                    />
                                </div>

                                <Button
                                    v-else
                                    type="button"
                                    variant="default"
                                    size="lg"
                                    :class="
                                        patientFormWizardFooterPrimaryButtonClass
                                    "
                                    :disabled="isSaving"
                                    :aria-label="
                                        plannedShareImageCount > 1
                                            ? t(
                                                  'patient.inventory.vacationSaveToPhotosMultiple',
                                                  {
                                                      count: String(
                                                          plannedShareImageCount,
                                                      ),
                                                  },
                                              )
                                            : t(
                                                  'patient.inventory.vacationSaveToPhotos',
                                              )
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
                                                ? t(
                                                      'patient.inventory.vacationSaving',
                                                  )
                                                : plannedShareImageCount > 1
                                                  ? t(
                                                        'patient.inventory.vacationSaveToPhotosMultiple',
                                                        {
                                                            count: String(
                                                                plannedShareImageCount,
                                                            ),
                                                        },
                                                    )
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
                                                      count: String(
                                                          savedShareImageCount,
                                                      ),
                                                  },
                                              )
                                            : t(
                                                  'patient.inventory.vacationSaveShareHint',
                                              )
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
                                        v-if="
                                            vacationSavedPackageHintUsesLiquidIcon
                                        "
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

                            <InventoryVacationExpiringPrescriptionsSection
                                v-if="props.expiring_prescriptions.length > 0"
                                :heading="
                                    t(
                                        'patient.inventory.vacationExpiringPrescriptionsHeading',
                                    )
                                "
                                :intro="
                                    t(
                                        'patient.inventory.vacationExpiringPrescriptionsIntro',
                                    )
                                "
                                :prescriptions="props.expiring_prescriptions"
                            />

                            <ul
                                v-if="props.result.items.length > 0"
                                class="flex flex-col gap-4"
                            >
                                <li
                                    v-for="item in props.result.items"
                                    :key="item.medication_id"
                                >
                                    <Card
                                        :class="
                                            inventoryVacationResultsCardClass
                                        "
                                    >
                                        <CardContent
                                            class="space-y-4 p-5 sm:p-6"
                                        >
                                            <p
                                                class="text-text-heading text-lg leading-snug font-bold"
                                            >
                                                {{ item.name }}
                                            </p>
                                            <InventoryVacationPickupBoxCalculator
                                                :id-prefix="`vacation-pickup-${item.medication_id}`"
                                                :dose-unit="
                                                    doseUnitForItem(
                                                        item.dose_unit,
                                                    )
                                                "
                                                :medication-type="
                                                    item.type_medication
                                                "
                                                :pickup-quantity="
                                                    item.pickup_quantity
                                                "
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
                                            props.result
                                                .skipped_medication_count,
                                        ),
                                    })
                                }}
                            </p>
                        </div>

                        <template #footer>
                            <div :class="patientFormWizardFooterRowClass">
                                <Button
                                    as-child
                                    variant="default"
                                    size="lg"
                                    :class="
                                        patientFormWizardFooterPrimaryButtonClass
                                    "
                                >
                                    <Link :href="route('patient.inventory')">
                                        {{
                                            t('patient.inventory.vacationDone')
                                        }}
                                    </Link>
                                </Button>
                            </div>
                        </template>
                    </PatientShellWizardScrollBody>
                </div>
            </PatientShellPageWizard>
        </div>
    </PatientLayout>
</template>
