<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationFormAmountWithUnitField from '@/Components/Patient/Medications/form/MedicationFormAmountWithUnitField.vue';
import type { MedicationCreateFormWithErrors } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { medicationDoseUnitChipForAmount } from '@/lib/patient/medications/options/medicationDoseUnitChipForAmount';
import { medicationDoseUnitRequiresStrength } from '@/lib/patient/medications/options/medicationDoseUnitForm';
import { medicationDoseUnitOptionsForSelect } from '@/lib/patient/medications/options/medicationDoseUnitOptions';
import { medicationStrengthUnitChipForAmount } from '@/lib/patient/medications/options/medicationStrengthUnitChipForAmount';
import type { MedicationStrengthUnitValue } from '@/lib/patient/medications/options/medicationStrengthUnitForm';
import { MEDICATION_STRENGTH_UNIT_OPTIONS } from '@/lib/patient/medications/options/medicationStrengthUnitForm';
import { defaultDoseUnitForMedicationType } from '@/lib/patient/medications/options/medicationTypeDefaultDoseUnit';
import { MEDICATION_TYPE_OPTIONS } from '@/lib/patient/medications/options/medicationTypeIcons';
import {
    patientFormFieldInputClass,
    patientFormFieldInvalidClass,
    patientFormLabelClass,
    patientFormLargeTouchFieldClass,
} from '@/lib/patient/patientFormFieldClasses';
import type { MedicationTypeValue } from '@/lib/types';
import { cn } from '@/lib/utils';

const { form, idPrefix } = defineProps<{
    form: MedicationCreateFormWithErrors;
    idPrefix: string;
}>();

const { t } = useI18n();

const doseUnitSelectOptions = computed(() =>
    medicationDoseUnitOptionsForSelect(form.dose_unit).map((opt) => ({
        value: opt.value,
        label: medicationDoseUnitChipForAmount(t, form.dose, opt.value),
    })),
);

const strengthUnitSelectOptions = computed(() =>
    MEDICATION_STRENGTH_UNIT_OPTIONS.map((opt) => ({
        value: opt.value,
        label: medicationStrengthUnitChipForAmount(opt.value),
    })),
);

const strengthIsRequired = computed(() =>
    medicationDoseUnitRequiresStrength(form.dose_unit),
);

const strengthFieldLabel = computed(() =>
    strengthIsRequired.value
        ? t('patient.medications.fields.strength')
        : t('patient.medications.fields.strengthOptional'),
);

const strengthAmountPlaceholder = computed(() =>
    t('patient.medications.fields.strengthAmountPlaceholder'),
);

const dosePlaceholder = computed(() => {
    const type = form.type_medication as MedicationTypeValue | '';

    if (type === 'pill') {
        return t('patient.medications.fields.dosePlaceholderPill');
    }

    if (type === 'liquid') {
        return t('patient.medications.fields.dosePlaceholderLiquid');
    }

    if (type === 'injection') {
        return t('patient.medications.fields.dosePlaceholderInjection');
    }

    if (type === 'sachets') {
        return t('patient.medications.fields.dosePlaceholderSachets');
    }

    return t('patient.medications.fields.dosePlaceholder');
});

watch(
    () => form.type_medication,
    () => {
        if (form.dose_unit === '') {
            const next = defaultDoseUnitForMedicationType(
                form.type_medication as MedicationTypeValue | '',
            );

            if (next !== null) {
                form.dose_unit = next;
            }
        }

        if (form.strength_unit === '') {
            form.strength_unit = defaultStrengthUnitForMedicationType(
                form.type_medication as MedicationTypeValue | '',
            );
        }
    },
);

watch(
    () => form.dose_unit,
    (doseUnit) => {
        if (form.strength_unit !== '') {
            return;
        }

        form.strength_unit =
            doseUnit === 'milliliter' ? 'milliliter' : 'milligram';
    },
);

function defaultStrengthUnitForMedicationType(
    type: MedicationTypeValue | '',
): MedicationStrengthUnitValue | '' {
    if (type === 'liquid') {
        return 'milliliter';
    }

    if (type === '') {
        return '';
    }

    return 'milligram';
}
</script>

<template>
    <div class="space-y-4 max-md:space-y-3.5 md:space-y-5 lg:space-y-8">
        <div>
            <Label
                :for="`${idPrefix}-name`"
                :class="cn(patientFormLabelClass, 'text-lg md:text-xl')"
            >
                {{ t('patient.medications.fields.name') }}
                <span class="text-danger">*</span>
            </Label>
            <Input
                :id="`${idPrefix}-name`"
                v-model="form.name"
                type="text"
                name="name"
                autocomplete="off"
                maxlength="500"
                :placeholder="t('patient.medications.fields.namePlaceholder')"
                :class="
                    cn(
                        patientFormFieldInputClass,
                        patientFormLargeTouchFieldClass,
                        'md:min-h-14 md:py-3! md:text-lg! md:leading-normal!',
                        form.errors.name ? patientFormFieldInvalidClass : null,
                    )
                "
                :aria-invalid="Boolean(form.errors.name)"
                :aria-describedby="
                    form.errors.name ? `${idPrefix}-name-error` : undefined
                "
            />
            <InputError
                :id="`${idPrefix}-name-error`"
                :message="form.errors.name"
            />
        </div>

        <div class="w-full min-w-0">
            <p
                :id="`${idPrefix}-type-label`"
                :class="cn(patientFormLabelClass, 'text-lg md:text-xl')"
            >
                {{ t('patient.medications.fields.type') }}
                <span class="text-danger">*</span>
            </p>
            <div
                :id="`${idPrefix}-type`"
                :class="
                    cn(
                        'grid touch-manipulation grid-cols-2 gap-x-2 gap-y-2 max-md:gap-y-2 md:gap-x-3 md:gap-y-4',
                        form.errors.type_medication &&
                            form.type_medication === ''
                            ? 'ring-danger/25 rounded-2xl p-0.5 ring-2'
                            : null,
                    )
                "
                role="radiogroup"
                :aria-labelledby="`${idPrefix}-type-label`"
                :aria-invalid="Boolean(form.errors.type_medication)"
                :aria-describedby="
                    form.errors.type_medication
                        ? `${idPrefix}-type-error`
                        : undefined
                "
            >
                <div
                    v-for="option in MEDICATION_TYPE_OPTIONS"
                    :key="option.type"
                    class="flex w-full flex-col items-center gap-1 md:gap-2"
                >
                    <div class="relative mx-auto w-full max-w-20 md:max-w-28">
                        <div
                            class="pointer-events-none pb-[100%]"
                            aria-hidden="true"
                        />
                        <button
                            :id="`${idPrefix}-type-option-${option.type}`"
                            type="button"
                            class="focus-visible:border-focus focus-visible:ring-focus/30 absolute inset-0 flex items-center justify-center rounded-xl border-2 p-2 transition-colors focus-visible:ring-2 focus-visible:outline-none md:rounded-2xl md:border-[3px] md:p-3 md:focus-visible:ring-4"
                            :class="
                                cn(
                                    form.type_medication === option.type
                                        ? 'border-primary bg-primary/12 text-primary'
                                        : 'border-border bg-surface text-text hover:bg-surface-hover',
                                )
                            "
                            :aria-pressed="form.type_medication === option.type"
                            :aria-labelledby="`${idPrefix}-type-option-${option.type}-label`"
                            @click="form.type_medication = option.type"
                        >
                            <span
                                class="inline-flex items-center justify-center text-inherit"
                                :class="
                                    form.type_medication === option.type
                                        ? null
                                        : 'text-text-muted'
                                "
                            >
                                <component
                                    :is="option.icon"
                                    class="size-9 shrink-0 text-inherit md:size-14"
                                    aria-hidden="true"
                                />
                            </span>
                        </button>
                    </div>
                    <span
                        :id="`${idPrefix}-type-option-${option.type}-label`"
                        class="font-body flex min-h-8 cursor-pointer touch-manipulation items-center justify-center px-0.5 text-center text-xs leading-snug font-bold wrap-break-word hyphens-auto select-none max-md:min-h-8 md:min-h-10 md:max-w-27 md:text-base md:leading-snug"
                        :class="
                            form.type_medication === option.type
                                ? 'text-text-heading'
                                : 'text-text'
                        "
                        @click="form.type_medication = option.type"
                    >
                        {{ t(`patient.medications.types.${option.type}`) }}
                    </span>
                </div>
            </div>
            <InputError
                :id="`${idPrefix}-type-error`"
                :message="form.errors.type_medication"
            />
        </div>

        <MedicationFormAmountWithUnitField
            v-model:amount="form.dose"
            v-model:unit="form.dose_unit"
            :id-prefix="idPrefix"
            :group-id="`${idPrefix}-dose-unit`"
            :amount-input-id="`${idPrefix}-dose`"
            :unit-select-id="`${idPrefix}-dose-unit-select`"
            amount-name="dose"
            :legend="t('patient.medications.fields.dose')"
            :amount-placeholder="dosePlaceholder"
            :unit-aria-label="t('patient.medications.fields.doseUnit')"
            :unit-options="doseUnitSelectOptions"
            :amount-error="form.errors.dose"
            :unit-error="form.errors.dose_unit"
            :amount-required="true"
            :unit-required="true"
            amount-input-mode="decimal"
        />

        <MedicationFormAmountWithUnitField
            v-model:amount="form.strength_amount"
            v-model:unit="form.strength_unit"
            :id-prefix="idPrefix"
            :group-id="`${idPrefix}-strength-unit`"
            :amount-input-id="`${idPrefix}-strength-amount`"
            :unit-select-id="`${idPrefix}-strength-unit-select`"
            amount-name="strength_amount"
            :legend="strengthFieldLabel"
            :amount-placeholder="strengthAmountPlaceholder"
            :unit-aria-label="t('patient.medications.fields.strengthUnit')"
            :unit-options="strengthUnitSelectOptions"
            amount-input-mode="decimal"
            :amount-error="form.errors.strength_amount ?? form.errors.strength"
            :unit-error="form.errors.strength_unit ?? form.errors.strength"
            :amount-required="strengthIsRequired"
            :unit-required="strengthIsRequired"
        />
    </div>
</template>
