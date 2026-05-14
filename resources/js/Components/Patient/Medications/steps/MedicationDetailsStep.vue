<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { useI18n } from 'vue-i18n';
import type { MedicationCreateFormWithErrors } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { MEDICATION_COLOR_OPTIONS } from '@/lib/patient/medications/options/medicationColorOptions';
import { medicationDoseUnitChipForAmount } from '@/lib/patient/medications/options/medicationDoseUnitChipForAmount';
import { MEDICATION_DOSE_UNIT_OPTIONS } from '@/lib/patient/medications/options/medicationDoseUnitOptions';
import { MEDICATION_TYPE_OPTIONS } from '@/lib/patient/medications/options/medicationTypeIcons';
import {
    patientFormFieldInputClass,
    patientFormFieldInvalidClass,
    patientFormLabelClass,
    patientFormLargeTouchFieldClass,
    patientFormSelectChevronStyle,
} from '@/lib/patient/patientFormFieldClasses';
import { MEDICATION_COLOR_HEX_VALUES } from '@/lib/types';
import { cn } from '@/lib/utils';

const standardMedicationColorHex = MEDICATION_COLOR_HEX_VALUES[0];

const { form, idPrefix } = defineProps<{
    form: MedicationCreateFormWithErrors;
    idPrefix: string;
}>();

const { t } = useI18n();
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

            <fieldset class="min-w-0 border-0 p-0">
                <legend :class="cn(patientFormLabelClass, 'text-lg md:text-xl')">
                    {{ t('patient.medications.fields.dose') }}
                </legend>
                <div
                    :id="`${idPrefix}-dose-unit`"
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
                        :placeholder="
                            t('patient.medications.fields.dosePlaceholder')
                        "
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
                                v-for="opt in MEDICATION_DOSE_UNIT_OPTIONS"
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

            <div
                class="flex flex-col gap-8 md:gap-6 lg:flex-row lg:items-start lg:gap-8"
            >
                <div class="min-w-0 w-full lg:flex-1">
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
                                            ? null
                                            : 'border-border bg-surface text-text hover:bg-surface-hover',
                                    )
                                "
                                :style="
                                    form.type_medication === option.type
                                        ? {
                                            borderColor: form.color,
                                              backgroundColor:
                                                  'color-mix(in srgb, ' +
                                                  form.color +
                                                  ' 14%, var(--color-surface))',
                                          }
                                        : undefined
                                "
                                :aria-pressed="form.type_medication === option.type"
                                :aria-labelledby="`${idPrefix}-type-option-${option.type}-label`"
                                @click="form.type_medication = option.type"
                            >
                                <span
                                    class="inline-flex items-center justify-center"
                                    :class="
                                        form.type_medication === option.type
                                            ? null
                                            : 'text-text-muted'
                                    "
                                    :style="
                                        form.type_medication === option.type
                                            ? { color: form.color }
                                            : undefined
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

            <div
                class="flex w-full flex-col items-center text-center lg:w-44 lg:shrink-0 lg:items-start lg:self-center lg:pt-0 lg:text-left"
            >
                <p
                    :id="`${idPrefix}-color-label`"
                    class="mb-2 w-full text-sm font-semibold leading-snug text-text-heading lg:text-base"
                >
                    {{ t('patient.medications.fields.color') }}
                </p>
                <div
                    :id="`${idPrefix}-color`"
                    :class="
                        cn(
                            'flex flex-wrap items-center justify-center gap-2 touch-manipulation lg:justify-start lg:gap-2.5',
                            form.errors.color
                                ? 'rounded-2xl p-0.5 ring-2 ring-danger/25'
                                : null,
                        )
                    "
                    role="radiogroup"
                    :aria-labelledby="`${idPrefix}-color-label`"
                    :aria-invalid="Boolean(form.errors.color)"
                    :aria-describedby="
                        form.errors.color ? `${idPrefix}-color-error` : undefined
                    "
                >
                    <button
                        v-for="opt in MEDICATION_COLOR_OPTIONS"
                        :id="`${idPrefix}-color-${opt.labelKey}`"
                        :key="opt.value"
                        type="button"
                        class="size-9 shrink-0 rounded-full border-2 border-transparent transition-transform focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-focus/30 md:size-10"
                        :class="
                            cn(
                                form.color === opt.value
                                    ? 'ring-2 ring-primary ring-offset-2 ring-offset-surface scale-105'
                                    : 'hover:scale-105 hover:ring-2 hover:ring-border/50',
                                opt.value === standardMedicationColorHex
                                    ? 'shadow-[inset_0_0_0_1px_rgba(255,255,255,0.35)]'
                                    : null,
                            )
                        "
                        :style="{ backgroundColor: opt.value }"
                        :aria-pressed="form.color === opt.value"
                        :aria-label="t(`patient.medications.colors.${opt.labelKey}`)"
                        @click="form.color = opt.value"
                    ></button>
                </div>
                <InputError
                    :id="`${idPrefix}-color-error`"
                    class="w-full max-w-md justify-center text-center text-sm lg:max-w-none lg:justify-start lg:text-left lg:text-base"
                    :message="form.errors.color"
                />
            </div>
            </div>
    </div>
</template>
