<script setup lang="ts">
import { Layers } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Label } from '@/Components/ui/label';
import {
    medicationStockCurrentStockIconWrapClass,
    medicationStockCurrentStockPanelClass,
} from '@/lib/patient/inventory/medicationStockCurrentStockPanelClasses';
import { medicationDoseUnitChipForAmount } from '@/lib/patient/medications/options/medicationDoseUnitChipForAmount';
import { formatMedicationStockDisplayAmount } from '@/lib/patient/medications/stock/formatMedicationStockDisplayAmount';
import { medicationStockPackageCountForQuantity } from '@/lib/patient/medications/stock/medicationStockPackageCountForQuantity';
import { parseMedicationStockNumericValue } from '@/lib/patient/medications/stock/parseMedicationStockNumericValue';
import {
    patientFormFieldInputClass,
    patientFormLabelClass,
    patientFormLargeTouchFieldClass,
} from '@/lib/patient/patientFormFieldClasses';
import type { MedicationDoseUnitValue } from '@/lib/types';
import { MEDICATION_DOSE_UNIT_VALUES } from '@/lib/types';
import { cn } from '@/lib/utils';

const props = defineProps<{
    idPrefix: string;
    doseUnit: string | null;
    medicationType: string;
    pickupQuantity: string;
    stockPiecesPerPackage: number | null;
}>();

const { t } = useI18n();

const numberOfBoxes = ref<string>('');
const piecesPerBox = ref<string>('');

const hasSavedPiecesPerPackage = computed(
    (): boolean =>
        props.stockPiecesPerPackage !== null && props.stockPiecesPerPackage > 0,
);

const isLiquidStock = computed(() => props.medicationType === 'liquid');

const primaryLabel = computed(() =>
    isLiquidStock.value
        ? t('patient.medications.stockCalculator.liquid.numberOfBottles')
        : t('patient.inventory.vacationPickupCalculator.minimumBoxesLabel'),
);

const secondaryLabel = computed(() =>
    isLiquidStock.value
        ? t('patient.medications.stockCalculator.liquid.mlPerBottle')
        : t('patient.medications.stockCalculator.piecesPerBox'),
);

const primaryPlaceholder = computed(() =>
    isLiquidStock.value
        ? t('patient.medications.stockCalculator.liquid.bottlesPlaceholder')
        : t('patient.medications.stockCalculator.boxesPlaceholder'),
);

const secondaryPlaceholder = computed(() =>
    isLiquidStock.value
        ? t('patient.medications.stockCalculator.liquid.mlPlaceholder')
        : t('patient.medications.stockCalculator.piecesPlaceholder'),
);

const pickupNumeric = computed((): number | null =>
    parseMedicationStockNumericValue(props.pickupQuantity, props.doseUnit),
);

const resolvedPiecesPerPackage = computed((): number | null => {
    if (hasSavedPiecesPerPackage.value) {
        return props.stockPiecesPerPackage;
    }

    const pieces = Number.parseInt(piecesPerBox.value, 10);

    if (Number.isNaN(pieces) || pieces <= 0) {
        return null;
    }

    return pieces;
});

const calculatedTotal = computed((): number | null => {
    const boxes = Number.parseInt(numberOfBoxes.value, 10);
    const pieces = resolvedPiecesPerPackage.value;

    if (Number.isNaN(boxes) || pieces === null || boxes <= 0) {
        return null;
    }

    return boxes * pieces;
});

const displayTotal = computed((): number | null => pickupNumeric.value);

const hasDisplayableDoseUnit = computed((): boolean => {
    if (props.doseUnit === null || props.doseUnit === '') {
        return false;
    }

    return (MEDICATION_DOSE_UNIT_VALUES as readonly string[]).includes(
        props.doseUnit,
    );
});

const doseUnitChip = computed((): string | null => {
    if (!hasDisplayableDoseUnit.value || displayTotal.value === null) {
        return null;
    }

    const chip = medicationDoseUnitChipForAmount(
        t,
        String(displayTotal.value),
        props.doseUnit as MedicationDoseUnitValue,
    );

    if (chip === '—') {
        return null;
    }

    return chip;
});

const totalMismatch = computed((): boolean => {
    const target = pickupNumeric.value;
    const calculated = calculatedTotal.value;

    if (target === null || calculated === null) {
        return false;
    }

    return calculated < target;
});

const totalPanelClass = computed((): string =>
    medicationStockCurrentStockPanelClass(null),
);

const totalIconWrapClass = computed((): string =>
    medicationStockCurrentStockIconWrapClass(null),
);

const boxFieldId = computed(() => `${props.idPrefix}-pickup-boxes`);
const piecesFieldId = computed(() => `${props.idPrefix}-pickup-pieces-per-box`);

function syncBoxesFromPickup(): void {
    const target = pickupNumeric.value;
    const pieces = resolvedPiecesPerPackage.value;

    if (target === null || target <= 0 || pieces === null) {
        return;
    }

    numberOfBoxes.value = String(
        medicationStockPackageCountForQuantity(target, pieces),
    );
}

watch(
    () => [pickupNumeric.value, props.stockPiecesPerPackage] as const,
    () => {
        if (hasSavedPiecesPerPackage.value) {
            piecesPerBox.value = String(props.stockPiecesPerPackage);
        }

        syncBoxesFromPickup();
    },
    { immediate: true },
);

watch(piecesPerBox, () => {
    if (hasSavedPiecesPerPackage.value) {
        return;
    }

    syncBoxesFromPickup();
});

function handleNumberInput(event: Event, target: 'boxes' | 'pieces'): void {
    if (target === 'pieces' && hasSavedPiecesPerPackage.value) {
        return;
    }

    const input = event.target as HTMLInputElement;
    const cleaned = input.value.replace(/\D/g, '');

    if (target === 'boxes') {
        numberOfBoxes.value = cleaned;
    } else {
        piecesPerBox.value = cleaned;
    }
}
</script>

<template>
    <div class="space-y-4 md:space-y-5">
        <div class="grid grid-cols-2 gap-3 md:gap-4">
            <div>
                <Label
                    :for="boxFieldId"
                    :class="cn(patientFormLabelClass, 'text-base sm:text-lg')"
                >
                    {{ primaryLabel }}
                </Label>
                <input
                    :id="boxFieldId"
                    :value="numberOfBoxes"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    autocomplete="off"
                    maxlength="6"
                    :placeholder="primaryPlaceholder"
                    :readonly="hasSavedPiecesPerPackage"
                    :class="
                        cn(
                            patientFormFieldInputClass,
                            patientFormLargeTouchFieldClass,
                            'mt-2 text-center text-2xl font-bold tabular-nums',
                            hasSavedPiecesPerPackage
                                ? 'cursor-default opacity-90'
                                : null,
                        )
                    "
                    @input="handleNumberInput($event, 'boxes')"
                />
            </div>

            <div>
                <Label
                    :for="piecesFieldId"
                    :class="cn(patientFormLabelClass, 'text-base sm:text-lg')"
                >
                    {{ secondaryLabel }}
                </Label>
                <input
                    :id="piecesFieldId"
                    :value="piecesPerBox"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    autocomplete="off"
                    maxlength="6"
                    :placeholder="secondaryPlaceholder"
                    :readonly="hasSavedPiecesPerPackage"
                    :class="
                        cn(
                            patientFormFieldInputClass,
                            patientFormLargeTouchFieldClass,
                            'mt-2 text-center text-2xl font-bold tabular-nums',
                            hasSavedPiecesPerPackage
                                ? 'cursor-default opacity-90'
                                : null,
                        )
                    "
                    @input="handleNumberInput($event, 'pieces')"
                />
            </div>
        </div>

        <output
            v-if="displayTotal !== null && displayTotal > 0"
            :class="totalPanelClass"
            :for="`${boxFieldId} ${piecesFieldId}`"
        >
            <div :class="totalIconWrapClass">
                <Layers class="size-5 sm:size-6" aria-hidden="true" />
            </div>
            <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                <span
                    class="text-text-heading text-sm leading-snug font-semibold sm:text-base"
                >
                    {{
                        t(
                            'patient.inventory.vacationPickupCalculator.totalLabel',
                        )
                    }}
                </span>
                <div class="flex items-baseline gap-2">
                    <span
                        class="text-text-heading text-2xl leading-none font-bold tracking-tight wrap-break-word whitespace-pre-wrap tabular-nums sm:text-3xl"
                    >
                        {{ displayTotal }}
                    </span>
                    <span
                        v-if="doseUnitChip !== null"
                        class="text-text-heading text-lg font-semibold sm:text-xl"
                    >
                        {{ doseUnitChip }}
                    </span>
                </div>
                <p
                    v-if="totalMismatch"
                    class="text-danger mt-1 text-sm leading-relaxed"
                >
                    {{
                        t(
                            'patient.inventory.vacationPickupCalculator.totalMismatch',
                            {
                                target: formatMedicationStockDisplayAmount(
                                    t,
                                    pickupQuantity,
                                    doseUnit,
                                ),
                            },
                        )
                    }}
                </p>
            </div>
        </output>
    </div>
</template>
