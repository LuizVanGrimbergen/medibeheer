<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import type { MedicationCreateFormWithErrors } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { medicationDoseUnitChipForAmount } from '@/lib/patient/medications/options/medicationDoseUnitChipForAmount';
import { medicationDoseUnitRequiresStrength } from '@/lib/patient/medications/options/medicationDoseUnitForm';
import { medicationDoseUnitOptionsForSelect } from '@/lib/patient/medications/options/medicationDoseUnitOptions';
import { MEDICATION_STRENGTH_UNIT_OPTIONS } from '@/lib/patient/medications/options/medicationStrengthUnitForm';
import { MEDICATION_TYPE_OPTIONS } from '@/lib/patient/medications/options/medicationTypeIcons';
import { defaultDoseUnitForMedicationType } from '@/lib/patient/medications/options/medicationTypeDefaultDoseUnit';
import {
    patientFormFieldInputClass,
    patientFormFieldInvalidClass,
    patientFormLabelClass,
    patientFormLargeTouchFieldClass,
    patientFormSelectChevronStyle,
} from '@/lib/patient/patientFormFieldClasses';
import type { MedicationTypeValue } from '@/lib/types';
import { cn } from '@/lib/utils';

const { form, idPrefix } = defineProps<{
    form: MedicationCreateFormWithErrors;
    idPrefix: string;
}>();

const { t } = useI18n();

const doseUnitSelectOptions = computed(() =>
    medicationDoseUnitOptionsForSelect(form.dose_unit),
);

const strengthIsRequired = computed(() =>
    medicationDoseUnitRequiresStrength(form.dose_unit),
);

const strengthFieldLabel = computed(() =>
    strengthIsRequired.value
        ? t('patient.medications.fields.strength')
        : t('patient.medications.fields.strengthOptional'),
);

const strengthPlaceholder = computed(() =>
    t('patient.medications.fields.strengthPlaceholder'),
);

const strengthAmountPlaceholder = computed(() =>
    t('patient.medications.fields.strengthAmountPlaceholder'),
);

const strengthCompositeHasError = computed(
    () =>
        Boolean(
            form.errors.strength ||
                form.errors.strength_amount ||
                form.errors.strength_unit,
        ),
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

    if (type === 'cream') {
        return t('patient.medications.fields.dosePlaceholderCream');
    }

    if (type === 'sachets') {
        return t('patient.medications.fields.dosePlaceholderSachets');
    }

    if (type === 'other') {
        return t('patient.medications.fields.dosePlaceholderOther');
    }

    return t('patient.medications.fields.dosePlaceholder');
});

watch(
    () => form.type_medication,
    () => {
        if (form.dose_unit !== '') {
            return;
        }

        const next = defaultDoseUnitForMedicationType(
            form.type_medication as MedicationTypeValue | '',
        );

        if (next !== null) {
            form.dose_unit = next;
        }
    },
);

watch(
    () => form.dose_unit,
    (doseUnit) => {
        if (!medicationDoseUnitRequiresStrength(doseUnit)) {
            form.strength_amount = '';
            form.strength_unit = '';

            return;
        }

        if (form.strength_unit !== '') {
            return;
        }

        form.strength_unit =
            doseUnit === 'injection' ? 'milliliter' : 'milligram';
    },
);
</script>

<template>
    <div class="space-y-6 md:space-y-5 lg:space-y-8">
        <div>
            <Label
                :for="`${idPrefix}-name`"
                :class="cn(patientFormLabelClass, 'text-lg md:text-xl')"
            >
                {{ t('patient.medications.fields.name') }}
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

        <div class="min-w-0 w-full">
                <p
                    :id="`${idPrefix}-type-label`"
                    :class="cn(patientFormLabelClass, 'text-lg md:text-xl')"
                >
                    {{ t('patient.medications.fields.type') }}
                </p>
                <div
                    :id="`${idPrefix}-type`"
                    :class="
                        cn(
                            'grid grid-cols-3 gap-x-2 gap-y-3 touch-manipulation md:gap-x-3 md:gap-y-3',
                            form.errors.type_medication && form.type_medication === ''
                                ? 'rounded-2xl p-0.5 ring-2 ring-danger/25'
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
                        <div class="relative mx-auto w-full max-w-24 md:max-w-28">
                            <div
                                class="pointer-events-none pb-[100%]"
                                aria-hidden="true"
                            />
                            <button
                                :id="`${idPrefix}-type-option-${option.type}`"
                                type="button"
                                class="absolute inset-0 flex items-center justify-center rounded-xl border-2 p-2 transition-colors focus-visible:border-focus focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus/30 md:rounded-2xl md:border-[3px] md:p-3 md:focus-visible:ring-4"
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
                                        class="size-11 shrink-0 text-inherit md:size-14"
                                        aria-hidden="true"
                                    />
                                </span>
                            </button>
                        </div>
                        <span
                            :id="`${idPrefix}-type-option-${option.type}-label`"
                            class="flex min-h-10 max-w-full cursor-pointer select-none items-center justify-center wrap-break-word hyphens-auto px-0.5 text-center font-body text-sm font-bold leading-snug touch-manipulation md:max-w-27 md:text-base md:leading-snug"
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

        <fieldset class="min-w-0 border-0 p-0">
            <legend :class="cn(patientFormLabelClass, 'text-lg md:text-xl')">
                {{ t('patient.medications.fields.dose') }}
            </legend>
            <div
                :id="`${idPrefix}-dose-unit`"
                class="mt-2"
                :class="
                    cn(
                        'flex min-h-14 w-full min-w-0 overflow-hidden rounded-2xl border-2 bg-surface transition-[border-color,box-shadow] touch-manipulation',
                        'focus-within:border-focus focus-within:ring-2 focus-within:ring-focus/25',
                        form.errors.dose || form.errors.dose_unit
                            ? 'border-danger ring-2 ring-danger/25'
                            : 'border-border',
                    )
                "
            >
                <input
                    :id="`${idPrefix}-dose`"
                    v-model="form.dose"
                    type="text"
                    name="dose"
                    required
                    autocomplete="off"
                    maxlength="500"
                    :placeholder="dosePlaceholder"
                    class="min-h-14 min-w-0 flex-1 border-0 bg-transparent px-4 py-3.5 text-lg leading-normal text-text placeholder:text-text-muted focus:outline-none focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    :aria-invalid="Boolean(form.errors.dose)"
                    aria-required="true"
                    :aria-describedby="
                        form.errors.dose ? `${idPrefix}-dose-error` : undefined
                    "
                />
                <div
                    class="relative flex shrink-0 self-stretch border-l-2 border-border"
                >
                    <select
                        :id="`${idPrefix}-dose-unit-select`"
                        v-model="form.dose_unit"
                        :aria-label="t('patient.medications.fields.doseUnit')"
                        class="min-h-14 min-w-22 max-w-40 cursor-pointer appearance-none border-0 bg-transparent bg-size-[1.25rem] bg-position-[right_0.75rem_center] bg-no-repeat py-3.5 pl-3 pr-11 text-base font-semibold leading-normal text-text-heading focus:outline-none focus-visible:outline-none touch-manipulation"
                        :style="patientFormSelectChevronStyle"
                        :aria-invalid="Boolean(form.errors.dose_unit)"
                        aria-required="true"
                        :aria-describedby="
                            form.errors.dose_unit
                                ? `${idPrefix}-dose-unit-error`
                                : undefined
                        "
                    >
                        <option
                            disabled
                            value=""
                        >
                            {{ t('patient.medications.fields.selectPlaceholder') }}
                        </option>
                        <option
                            v-for="opt in doseUnitSelectOptions"
                            :key="opt.value"
                            :value="opt.value"
                        >
                            {{
                                medicationDoseUnitChipForAmount(t, form.dose, opt.value)
                            }}
                        </option>
                    </select>
                </div>
            </div>
            <InputError
                :id="`${idPrefix}-dose-error`"
                :message="form.errors.dose"
            />
            <InputError
                :id="`${idPrefix}-dose-unit-error`"
                :message="form.errors.dose_unit"
            />
        </fieldset>

        <fieldset
            v-if="strengthIsRequired"
            class="min-w-0 border-0 p-0"
        >
            <legend :class="cn(patientFormLabelClass, 'text-lg md:text-xl')">
                {{ strengthFieldLabel }}
            </legend>
            <div
                :id="`${idPrefix}-strength-unit`"
                class="mt-2"
                :class="
                    cn(
                        'flex min-h-14 w-full min-w-0 overflow-hidden rounded-2xl border-2 bg-surface transition-[border-color,box-shadow] touch-manipulation',
                        'focus-within:border-focus focus-within:ring-2 focus-within:ring-focus/25',
                        strengthCompositeHasError
                            ? 'border-danger ring-2 ring-danger/25'
                            : 'border-border',
                    )
                "
            >
                <input
                    :id="`${idPrefix}-strength-amount`"
                    v-model="form.strength_amount"
                    type="text"
                    name="strength_amount"
                    required
                    autocomplete="off"
                    maxlength="500"
                    inputmode="decimal"
                    :placeholder="strengthAmountPlaceholder"
                    class="min-h-14 min-w-0 flex-1 border-0 bg-transparent px-4 py-3.5 text-lg leading-normal text-text placeholder:text-text-muted focus:outline-none focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    :aria-invalid="Boolean(form.errors.strength_amount || form.errors.strength)"
                    aria-required="true"
                    :aria-describedby="
                        form.errors.strength_amount || form.errors.strength
                            ? `${idPrefix}-strength-amount-error`
                            : undefined
                    "
                />
                <div
                    class="relative flex shrink-0 self-stretch border-l-2 border-border"
                >
                    <select
                        :id="`${idPrefix}-strength-unit-select`"
                        v-model="form.strength_unit"
                        :aria-label="t('patient.medications.fields.strengthUnit')"
                        class="min-h-14 min-w-22 max-w-40 cursor-pointer appearance-none border-0 bg-transparent bg-size-[1.25rem] bg-position-[right_0.75rem_center] bg-no-repeat py-3.5 pl-3 pr-11 text-base font-semibold leading-normal text-text-heading focus:outline-none focus-visible:outline-none touch-manipulation"
                        :style="patientFormSelectChevronStyle"
                        required
                        :aria-invalid="Boolean(form.errors.strength_unit || form.errors.strength)"
                        aria-required="true"
                        :aria-describedby="
                            form.errors.strength_unit || form.errors.strength
                                ? `${idPrefix}-strength-unit-error`
                                : undefined
                        "
                    >
                        <option
                            disabled
                            value=""
                        >
                            {{ t('patient.medications.fields.selectPlaceholder') }}
                        </option>
                        <option
                            v-for="opt in MEDICATION_STRENGTH_UNIT_OPTIONS"
                            :key="opt.value"
                            :value="opt.value"
                        >
                            {{
                                medicationDoseUnitChipForAmount(
                                    t,
                                    form.strength_amount,
                                    opt.value,
                                )
                            }}
                        </option>
                    </select>
                </div>
            </div>
            <InputError
                :id="`${idPrefix}-strength-amount-error`"
                :message="form.errors.strength_amount ?? form.errors.strength"
            />
            <InputError
                :id="`${idPrefix}-strength-unit-error`"
                :message="form.errors.strength_unit"
            />
        </fieldset>

        <div v-else>
            <Label
                :for="`${idPrefix}-strength`"
                :class="cn(patientFormLabelClass, 'text-lg md:text-xl')"
            >
                {{ strengthFieldLabel }}
            </Label>
            <Input
                :id="`${idPrefix}-strength`"
                v-model="form.strength"
                type="text"
                name="strength"
                autocomplete="off"
                maxlength="500"
                :placeholder="strengthPlaceholder"
                class="mt-2"
                :class="
                    cn(
                        patientFormFieldInputClass,
                        patientFormLargeTouchFieldClass,
                        'md:min-h-14 md:py-3! md:text-lg! md:leading-normal!',
                        form.errors.strength ? patientFormFieldInvalidClass : null,
                    )
                "
                :aria-invalid="Boolean(form.errors.strength)"
                :aria-describedby="
                    form.errors.strength ? `${idPrefix}-strength-error` : undefined
                "
            />
            <InputError
                :id="`${idPrefix}-strength-error`"
                :message="form.errors.strength"
            />
        </div>
    </div>
</template>
