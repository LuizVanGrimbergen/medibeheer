<script setup lang="ts">
import { Layers } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import type { MedicationStockProgressTone } from '@/lib/patient/inventory/medicationListVisualTone';
import {
    medicationStockCurrentStockIconWrapClass,
    medicationStockCurrentStockPanelClass,
} from '@/lib/patient/inventory/medicationStockCurrentStockPanelClasses';
import { medicationDoseUnitChipForAmount } from '@/lib/patient/medications/options/medicationDoseUnitChipForAmount';
import { parseMedicationStockNumericValue } from '@/lib/patient/medications/stock/parseMedicationStockNumericValue';
import {
    patientFormFieldInputClass,
    patientFormFieldInvalidClass,
    patientFormLabelClass,
    patientFormLargeTouchFieldClass,
} from '@/lib/patient/patientFormFieldClasses';
import type { MedicationDoseUnitValue } from '@/lib/types';
import { MEDICATION_DOSE_UNIT_VALUES } from '@/lib/types';
import { cn } from '@/lib/utils';

const amountToAdd = defineModel<string>('amountToAdd', { required: true });

const props = withDefaults(
    defineProps<{
        idPrefix: string;
        doseUnit: string | null;
        currentStock: string;
        stockProgressTone?: MedicationStockProgressTone | null;
        stockPiecesPerPackage?: number | null;
        errorMessage?: string;
    }>(),
    {
        stockProgressTone: null,
        stockPiecesPerPackage: null,
    },
);

const { t } = useI18n();

const numberOfBoxes = ref<string>('');
const piecesPerBox = ref<string>('');

const calculatedAddAmount = computed((): number | null => {
    const boxes = Number.parseInt(numberOfBoxes.value, 10);
    const pieces = Number.parseInt(piecesPerBox.value, 10);

    if (
        Number.isNaN(boxes) ||
        Number.isNaN(pieces) ||
        boxes <= 0 ||
        pieces <= 0
    ) {
        return null;
    }

    return boxes * pieces;
});

watch(
    () => props.stockPiecesPerPackage,
    (piecesPerPackage) => {
        if (piecesPerPackage !== null && piecesPerPackage > 0) {
            piecesPerBox.value = String(piecesPerPackage);
        }
    },
    { immediate: true },
);

watch(calculatedAddAmount, (newTotal) => {
    if (newTotal !== null && newTotal > 0) {
        amountToAdd.value = String(newTotal);
    } else {
        amountToAdd.value = '';
    }
});

const currentStockNumeric = computed((): number | null => {
    return parseMedicationStockNumericValue(props.currentStock, props.doseUnit);
});

const newTotalStock = computed((): number | null => {
    const current = currentStockNumeric.value;
    const add = calculatedAddAmount.value;

    if (current === null || add === null) {
        return null;
    }

    return current + add;
});

const hasDisplayableDoseUnit = computed((): boolean => {
    if (props.doseUnit === null || props.doseUnit === '') {
        return false;
    }

    return (MEDICATION_DOSE_UNIT_VALUES as readonly string[]).includes(
        props.doseUnit,
    );
});

const doseUnitChipForAmount = (amount: number): string | null => {
    if (!hasDisplayableDoseUnit.value) {
        return null;
    }

    const chip = medicationDoseUnitChipForAmount(
        t,
        String(amount),
        props.doseUnit as MedicationDoseUnitValue,
    );

    if (chip === '—') {
        return null;
    }

    return chip;
};

const currentStockDoseUnitChip = computed((): string | null => {
    if (currentStockNumeric.value === null) {
        return null;
    }

    return doseUnitChipForAmount(currentStockNumeric.value);
});

const newTotalDoseUnitChip = computed((): string | null => {
    if (newTotalStock.value === null) {
        return null;
    }

    return doseUnitChipForAmount(newTotalStock.value);
});

const currentStockAmountDisplay = computed((): string | null => {
    if (currentStockNumeric.value !== null) {
        const raw = props.currentStock.trim();

        if (raw.includes(',')) {
            return String(currentStockNumeric.value).replace('.', ',');
        }

        return String(currentStockNumeric.value);
    }

    const trimmed = props.currentStock.trim();

    return trimmed.length > 0 ? trimmed : null;
});

const currentStockPanelClass = computed((): string =>
    medicationStockCurrentStockPanelClass(props.stockProgressTone ?? null),
);

const currentStockIconWrapClass = computed((): string =>
    medicationStockCurrentStockIconWrapClass(props.stockProgressTone ?? null),
);

const newTotalStockPanelClass = computed((): string =>
    medicationStockCurrentStockPanelClass('safe'),
);

const newTotalStockIconWrapClass = computed((): string =>
    medicationStockCurrentStockIconWrapClass('safe'),
);

const boxFieldId = computed(() => `${props.idPrefix}-refill-boxes`);
const piecesFieldId = computed(() => `${props.idPrefix}-refill-pieces-per-box`);

const describedById = computed((): string | undefined => {
    if (props.errorMessage !== undefined && props.errorMessage.length > 0) {
        return `${props.idPrefix}-refill-error`;
    }

    return undefined;
});

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
        <div :class="currentStockPanelClass">
            <div :class="currentStockIconWrapClass">
                <Layers class="size-5 sm:size-6" aria-hidden="true" />
            </div>
            <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                <span
                    class="text-text-heading text-sm leading-snug font-semibold sm:text-base"
                >
                    {{ t('patient.medications.fields.currentStock') }}
                </span>
                <div class="flex items-baseline gap-2">
                    <span
                        v-if="currentStockAmountDisplay !== null"
                        class="text-text-heading text-2xl leading-none font-bold tracking-tight wrap-break-word whitespace-pre-wrap tabular-nums sm:text-3xl"
                    >
                        {{ currentStockAmountDisplay }}
                    </span>
                    <span
                        v-if="currentStockDoseUnitChip !== null"
                        class="text-text-heading text-lg font-semibold sm:text-xl"
                    >
                        {{ currentStockDoseUnitChip }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 md:gap-4">
            <div>
                <Label
                    :for="boxFieldId"
                    :class="cn(patientFormLabelClass, 'text-base sm:text-lg')"
                >
                    {{ t('patient.inventory.addStockBoxesLabel') }}
                </Label>
                <input
                    :id="boxFieldId"
                    :value="numberOfBoxes"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    autocomplete="off"
                    maxlength="6"
                    :placeholder="
                        t('patient.inventory.addStockBoxesPlaceholder')
                    "
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
                    {{ t('patient.inventory.addStockPiecesPerBoxLabel') }}
                </Label>
                <input
                    :id="piecesFieldId"
                    :value="piecesPerBox"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    autocomplete="off"
                    maxlength="6"
                    :placeholder="
                        t('patient.inventory.addStockPiecesPerBoxPlaceholder')
                    "
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
            v-if="newTotalStock !== null"
            :class="newTotalStockPanelClass"
            :for="`${boxFieldId} ${piecesFieldId}`"
        >
            <div :class="newTotalStockIconWrapClass">
                <Layers class="size-5 sm:size-6" aria-hidden="true" />
            </div>
            <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                <span
                    class="text-text-heading text-sm leading-snug font-semibold sm:text-base"
                >
                    {{ t('patient.inventory.addStockNewTotal') }}
                </span>
                <div class="flex items-baseline gap-2">
                    <span
                        class="text-text-heading text-2xl leading-none font-bold tracking-tight wrap-break-word whitespace-pre-wrap tabular-nums sm:text-3xl"
                    >
                        {{ newTotalStock }}
                    </span>
                    <span
                        v-if="newTotalDoseUnitChip !== null"
                        class="text-text-heading text-lg font-semibold sm:text-xl"
                    >
                        {{ newTotalDoseUnitChip }}
                    </span>
                </div>
            </div>
        </output>

        <InputError :id="`${idPrefix}-refill-error`" :message="errorMessage" />
    </div>
</template>
