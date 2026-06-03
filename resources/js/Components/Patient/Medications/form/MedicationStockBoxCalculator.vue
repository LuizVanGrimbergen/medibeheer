<script setup lang="ts">
import type { LucideIcon } from 'lucide-vue-next';
import { Layers, Package, PillBottle } from 'lucide-vue-next';
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
const piecesPerPackageModel = defineModel<string>('piecesPerPackage', {
    default: '',
});

const props = defineProps<{
    idPrefix: string;
    doseUnit: string;
    medicationType?: string;
    errorMessage?: string;
}>();

const isLiquidStock = computed(() => props.medicationType === 'liquid');

const stockCalculatorHintIcon = computed(
    (): LucideIcon => (isLiquidStock.value ? PillBottle : Package),
);

const stockCalculatorHint = computed(() =>
    isLiquidStock.value
        ? t('patient.medications.stockCalculator.liquid.hint')
        : t('patient.medications.stockCalculator.hint'),
);

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

const numberOfBoxes = ref<string>('');
const piecesPerBox = ref<string>('');

const calculatedTotal = computed((): number | null => {
    const boxes = Number.parseInt(numberOfBoxes.value, 10);
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
    } else if (numberOfBoxes.value === '' && piecesPerBox.value === '') {
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
            numberOfBoxes.value === '' &&
            piecesPerBox.value === '' &&
            newValue !== ''
        ) {
            const parsed = Number.parseInt(newValue, 10);

            if (!Number.isNaN(parsed) && parsed > 0) {
                numberOfBoxes.value = '1';
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

        numberOfBoxes.value = '';
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

const describedById = computed((): string | undefined => {
    if (props.errorMessage !== undefined && props.errorMessage.length > 0) {
        return `${props.idPrefix}-stock-error`;
    }

    return undefined;
});

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
        numberOfBoxes.value = cleaned;
    } else {
        piecesPerBox.value = cleaned;
    }
}
</script>

<template>
    <div class="space-y-4 md:space-y-5">
        <div
            class="border-border flex w-full min-w-0 items-start gap-3.5 rounded-2xl border-2 bg-white px-4 py-3.5 sm:gap-4 sm:px-5 sm:py-4"
        >
            <div
                class="bg-primary/12 text-primary flex size-11 shrink-0 items-center justify-center rounded-xl sm:size-14 sm:rounded-2xl"
            >
                <component
                    :is="stockCalculatorHintIcon"
                    class="size-5 sm:size-6"
                    aria-hidden="true"
                />
            </div>
            <div class="flex min-w-0 flex-1 flex-col gap-1">
                <span
                    class="text-sm leading-snug font-semibold text-black sm:text-base"
                >
                    {{ stockCalculatorHint }}
                </span>
            </div>
        </div>

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
                    :value="numberOfBoxes"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    autocomplete="off"
                    maxlength="6"
                    :placeholder="stockCalculatorPrimaryPlaceholder"
                    :class="
                        cn(
                            patientFormFieldInputClass,
                            patientFormLargeTouchFieldClass,
                            'mt-2 text-center text-2xl font-bold tabular-nums',
                            errorMessage ? patientFormFieldInvalidClass : null,
                        )
                    "
                    :aria-invalid="Boolean(errorMessage)"
                    :aria-describedby="describedById"
                    @input="handleNumberInput($event, 'boxes')"
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
                    :placeholder="stockCalculatorSecondaryPlaceholder"
                    :class="
                        cn(
                            patientFormFieldInputClass,
                            patientFormLargeTouchFieldClass,
                            'mt-2 text-center text-2xl font-bold tabular-nums',
                            errorMessage ? patientFormFieldInvalidClass : null,
                        )
                    "
                    :aria-invalid="Boolean(errorMessage)"
                    :aria-describedby="describedById"
                    @input="handleNumberInput($event, 'pieces')"
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

        <InputError :id="`${idPrefix}-stock-error`" :message="errorMessage" />
    </div>
</template>
