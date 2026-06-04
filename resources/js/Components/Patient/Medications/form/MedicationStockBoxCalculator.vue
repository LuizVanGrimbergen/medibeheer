<script setup lang="ts">
import { Layers } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import {
    medicationStockCurrentStockIconWrapClass,
    medicationStockCurrentStockPanelClass,
} from '@/lib/patient/inventory/medicationStockCurrentStockPanelClasses';
import { medicationDoseUnitChipForAmount } from '@/lib/patient/medications/options/medicationDoseUnitChipForAmount';
import {
    patientFormFieldInputClass,
    patientFormFieldInvalidClass,
    patientFormLabelClass,
    patientFormLargeTouchFieldClass,
} from '@/lib/patient/patientFormFieldClasses';
import type { MedicationDoseUnitValue } from '@/lib/types';
import { MEDICATION_DOSE_UNIT_VALUES } from '@/lib/types';
import { cn } from '@/lib/utils';

const model = defineModel<string>({ required: true });
const numberOfBoxesModel = defineModel<string>('numberOfBoxes', {
    default: '',
});
const piecesPerPackageModel = defineModel<string>('piecesPerPackage', {
    default: '',
});

const props = defineProps<{
    idPrefix: string;
    doseUnit: string;
    medicationType?: string;
    boxesError?: string;
    piecesError?: string;
}>();

const isLiquidStock = computed(() => props.medicationType === 'liquid');

const stockCalculatorPrimaryLabel = computed(() =>
    isLiquidStock.value
        ? t('patient.medications.stockCalculator.liquid.numberOfBottles')
        : t('patient.medications.stockCalculator.numberOfBoxes'),
);

const stockCalculatorSecondaryLabel = computed(() =>
    isLiquidStock.value
        ? t('patient.medications.stockCalculator.liquid.mlPerBottle')
        : t('patient.medications.stockCalculator.piecesPerBox'),
);

const stockCalculatorPrimaryPlaceholder = computed(() =>
    isLiquidStock.value
        ? t('patient.medications.stockCalculator.liquid.bottlesPlaceholder')
        : t('patient.medications.stockCalculator.boxesPlaceholder'),
);

const stockCalculatorSecondaryPlaceholder = computed(() =>
    isLiquidStock.value
        ? t('patient.medications.stockCalculator.liquid.mlPlaceholder')
        : t('patient.medications.stockCalculator.piecesPlaceholder'),
);

const { t } = useI18n();

const piecesPerBox = ref<string>('');

const calculatedTotal = computed((): number | null => {
    const boxes = Number.parseInt(numberOfBoxesModel.value, 10);
    const pieces = Number.parseInt(piecesPerBox.value, 10);

    if (
        Number.isNaN(boxes) ||
        Number.isNaN(pieces) ||
        boxes < 0 ||
        pieces < 0
    ) {
        return null;
    }

    return boxes * pieces;
});

watch(calculatedTotal, (newTotal) => {
    if (newTotal !== null && newTotal >= 0) {
        model.value = String(newTotal);
    } else if (
        numberOfBoxesModel.value === '' &&
        piecesPerBox.value === ''
    ) {
        model.value = '';
    }
});

watch(piecesPerBox, (value) => {
    piecesPerPackageModel.value = value;
});

watch(
    piecesPerPackageModel,
    (value) => {
        if (value.trim().length > 0) {
            piecesPerBox.value = value.trim();
        }
    },
    { immediate: true },
);

watch(
    model,
    (newValue) => {
        if (
            numberOfBoxesModel.value === '' &&
            piecesPerBox.value === '' &&
            newValue !== ''
        ) {
            const parsed = Number.parseInt(newValue, 10);

            if (!Number.isNaN(parsed) && parsed > 0) {
                numberOfBoxesModel.value = '1';
                piecesPerBox.value = String(parsed);
            }
        }
    },
    { immediate: true },
);

const previousMedicationType = ref<string | undefined>(undefined);

watch(
    () => props.medicationType,
    (newType) => {
        const previous = previousMedicationType.value;
        previousMedicationType.value = newType;

        if (previous === undefined) {
            return;
        }

        const wasLiquid = previous === 'liquid';
        const isLiquid = newType === 'liquid';

        if (wasLiquid === isLiquid) {
            return;
        }

        numberOfBoxesModel.value = '';
        piecesPerBox.value = '';
        model.value = '';
    },
);

const hasDisplayableDoseUnit = computed((): boolean => {
    if (props.doseUnit === '') {
        return false;
    }

    return (MEDICATION_DOSE_UNIT_VALUES as readonly string[]).includes(
        props.doseUnit,
    );
});

const doseUnitChip = computed((): string | null => {
    if (!hasDisplayableDoseUnit.value || calculatedTotal.value === null) {
        return null;
    }

    const chip = medicationDoseUnitChipForAmount(
        t,
        String(calculatedTotal.value),
        props.doseUnit as MedicationDoseUnitValue,
    );

    if (chip === '—') {
        return null;
    }

    return chip;
});

const boxFieldId = computed(() => `${props.idPrefix}-stock-boxes`);
const piecesFieldId = computed(() => `${props.idPrefix}-stock-pieces-per-box`);

function fieldDescribedBy(
    field: 'boxes' | 'pieces',
    error: string | undefined,
): string | undefined {
    if (error === undefined || error.length < 1) {
        return undefined;
    }

    return `${props.idPrefix}-stock-${field}-error`;
}

const totalStockPanelClass = computed((): string =>
    medicationStockCurrentStockPanelClass(null),
);

const totalStockIconWrapClass = computed((): string =>
    medicationStockCurrentStockIconWrapClass(null),
);

function handleNumberInput(event: Event, target: 'boxes' | 'pieces'): void {
    const input = event.target as HTMLInputElement;
    const cleaned = input.value.replace(/\D/g, '');

    if (target === 'boxes') {
        numberOfBoxesModel.value = cleaned;
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
                    {{ stockCalculatorPrimaryLabel }}
                </Label>
                <input
                    :id="boxFieldId"
                    :value="numberOfBoxesModel"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    autocomplete="off"
                    maxlength="6"
                    aria-required="true"
                    :placeholder="stockCalculatorPrimaryPlaceholder"
                    :class="
                        cn(
                            patientFormFieldInputClass,
                            patientFormLargeTouchFieldClass,
                            'mt-2 text-center text-2xl font-bold tabular-nums',
                            props.boxesError
                                ? patientFormFieldInvalidClass
                                : null,
                        )
                    "
                    :aria-invalid="Boolean(props.boxesError)"
                    :aria-describedby="
                        fieldDescribedBy('boxes', props.boxesError)
                    "
                    @input="handleNumberInput($event, 'boxes')"
                />
                <InputError
                    :id="`${props.idPrefix}-stock-boxes-error`"
                    class="mt-2"
                    :message="props.boxesError"
                />
            </div>

            <div>
                <Label
                    :for="piecesFieldId"
                    :class="cn(patientFormLabelClass, 'text-base sm:text-lg')"
                >
                    {{ stockCalculatorSecondaryLabel }}
                </Label>
                <input
                    :id="piecesFieldId"
                    :value="piecesPerBox"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    autocomplete="off"
                    maxlength="6"
                    aria-required="true"
                    :placeholder="stockCalculatorSecondaryPlaceholder"
                    :class="
                        cn(
                            patientFormFieldInputClass,
                            patientFormLargeTouchFieldClass,
                            'mt-2 text-center text-2xl font-bold tabular-nums',
                            props.piecesError
                                ? patientFormFieldInvalidClass
                                : null,
                        )
                    "
                    :aria-invalid="Boolean(props.piecesError)"
                    :aria-describedby="
                        fieldDescribedBy('pieces', props.piecesError)
                    "
                    @input="handleNumberInput($event, 'pieces')"
                />
                <InputError
                    :id="`${props.idPrefix}-stock-pieces-error`"
                    class="mt-2"
                    :message="props.piecesError"
                />
            </div>
        </div>

        <output
            v-if="calculatedTotal !== null"
            :class="totalStockPanelClass"
            :for="`${boxFieldId} ${piecesFieldId}`"
        >
            <div :class="totalStockIconWrapClass">
                <Layers class="size-5 sm:size-6" aria-hidden="true" />
            </div>
            <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                <span
                    class="text-text-heading text-sm leading-snug font-semibold sm:text-base"
                >
                    {{
                        t('patient.medications.stockCalculator.totalStockLabel')
                    }}
                </span>
                <div class="flex items-baseline gap-2">
                    <span
                        class="text-text-heading text-2xl leading-none font-bold tracking-tight wrap-break-word whitespace-pre-wrap tabular-nums sm:text-3xl"
                    >
                        {{ calculatedTotal }}
                    </span>
                    <span
                        v-if="doseUnitChip !== null"
                        class="text-text-heading text-lg font-semibold sm:text-xl"
                    >
                        {{ doseUnitChip }}
                    </span>
                </div>
            </div>
        </output>

    </div>
</template>
