<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
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

const props = defineProps<{
    idPrefix: string;
    fieldIdSuffix: 'current-stock';
    labelKey: 'patient.medications.fields.currentStock';
    placeholderExampleAmount: string;
    fallbackPlaceholderKey: 'patient.medications.fields.currentStockPlaceholder';
    doseUnit: string;
    maxlength: number;
    errorMessage?: string;
}>();

const { t } = useI18n();

const fieldId = computed(() => `${props.idPrefix}-${props.fieldIdSuffix}`);

const inputName = computed(() => props.fieldIdSuffix.replaceAll('-', '_'));

const hasDisplayableDoseUnit = computed((): boolean => {
    if (props.doseUnit === '') {
        return false;
    }

    return (MEDICATION_DOSE_UNIT_VALUES as readonly string[]).includes(
        props.doseUnit,
    );
});

const doseUnitChip = computed((): string | null => {
    if (!hasDisplayableDoseUnit.value) {
        return null;
    }

    const chip = medicationDoseUnitChipForAmount(
        t,
        model.value,
        props.doseUnit as MedicationDoseUnitValue,
    );

    if (chip === '—') {
        return null;
    }

    return chip;
});

const placeholder = computed((): string => {
    if (!hasDisplayableDoseUnit.value) {
        return t(props.fallbackPlaceholderKey);
    }

    return t('patient.medications.fields.stockPlaceholderAmountOnly', {
        example: props.placeholderExampleAmount,
    });
});

const describedById = computed((): string | undefined => {
    if (props.errorMessage !== undefined && props.errorMessage.length > 0) {
        return `${fieldId.value}-error`;
    }

    return undefined;
});
</script>

<template>
    <div>
        <Label :for="fieldId" :class="cn(patientFormLabelClass, 'text-xl')">
            {{ t(labelKey) }}
        </Label>
        <div
            v-if="hasDisplayableDoseUnit && doseUnitChip !== null"
            :id="`${fieldId}-dose-unit`"
            class="mt-2"
            :class="
                cn(
                    'bg-surface flex min-h-14 w-full min-w-0 touch-manipulation overflow-hidden rounded-2xl border-2 transition-[border-color,box-shadow]',
                    'focus-within:border-focus focus-within:ring-focus/25 focus-within:ring-2',
                    errorMessage
                        ? 'border-danger ring-danger/25 ring-2'
                        : 'border-border',
                )
            "
        >
            <input
                :id="fieldId"
                v-model="model"
                type="text"
                :name="inputName"
                autocomplete="off"
                :maxlength="maxlength"
                :placeholder="placeholder"
                class="text-text placeholder:text-text-muted min-h-14 min-w-0 flex-1 border-0 bg-transparent px-4 py-3.5 text-lg leading-normal focus:outline-none focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                :class="patientFormLargeTouchFieldClass"
                :aria-invalid="Boolean(errorMessage)"
                :aria-describedby="describedById"
            />
            <div
                class="border-border flex shrink-0 items-center self-stretch border-l-2 px-4"
                aria-hidden="true"
            >
                <span
                    class="text-text-heading text-base leading-normal font-semibold"
                >
                    {{ doseUnitChip }}
                </span>
            </div>
        </div>
        <input
            v-else
            :id="fieldId"
            v-model="model"
            type="text"
            :name="inputName"
            autocomplete="off"
            :maxlength="maxlength"
            :placeholder="placeholder"
            :class="
                cn(
                    patientFormFieldInputClass,
                    patientFormLargeTouchFieldClass,
                    'mt-2',
                    errorMessage ? patientFormFieldInvalidClass : null,
                )
            "
            :aria-invalid="Boolean(errorMessage)"
            :aria-describedby="describedById"
        />
        <InputError :id="`${fieldId}-error`" :message="errorMessage" />
    </div>
</template>
