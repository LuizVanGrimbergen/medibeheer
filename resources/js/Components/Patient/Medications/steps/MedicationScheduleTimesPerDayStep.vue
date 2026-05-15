<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import type { MedicationCreateFormWithErrors } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import {
    patientFormFieldInvalidClass,
    patientFormLabelClass,
    patientFormSelectBaseClass,
    patientFormSelectChevronStyle,
} from '@/lib/patient/patientFormFieldClasses';
import { cn } from '@/lib/utils';

const TIMES_PER_DAY_PRESET_VALUES = ['1', '2', '3', '4'] as const;

const TIMES_PER_DAY_CUSTOM_OPTIONS = Array.from({ length: 20 }, (_, index) => index + 5);

const { form, idPrefix } = defineProps<{
    form: MedicationCreateFormWithErrors;
    idPrefix: string;
}>();

const { t } = useI18n();

function timesPerDayOptionLabel(count: number): string {
    return t('patient.medications.timesPerDay.nTimesPerDay', { n: count });
}

function isTimesPerDayPresetValue(value: string): boolean {
    return (TIMES_PER_DAY_PRESET_VALUES as readonly string[]).includes(value.trim());
}

function isTimesPerDayCustomValue(value: string): boolean {
    if (!/^\d+$/.test(value.trim())) {
        return false;
    }

    const count = Number(value.trim());

    return Number.isInteger(count) && count >= 5 && count <= 24;
}

const prefersCustomTimesPerDay = ref(isTimesPerDayCustomValue(form.schedule.times_per_day));

const showCustomTimesPerDaySelect = computed(
    () =>
        prefersCustomTimesPerDay.value ||
        isTimesPerDayCustomValue(form.schedule.times_per_day),
);

const selectTimesPerDayPreset = (value: (typeof TIMES_PER_DAY_PRESET_VALUES)[number]): void => {
    prefersCustomTimesPerDay.value = false;
    form.schedule.times_per_day = value;
};

const selectCustomTimesPerDay = (): void => {
    prefersCustomTimesPerDay.value = true;
};

watch(
    () => form.schedule.times_per_day,
    (value) => {
        if (prefersCustomTimesPerDay.value) {
            return;
        }

        if (!isTimesPerDayPresetValue(value) && value.trim().length > 0) {
            prefersCustomTimesPerDay.value = true;
        }
    },
);

const timesPerDayCustomSelect = computed({
    get(): string {
        const trimmed = form.schedule.times_per_day.trim();

        if (!/^\d+$/.test(trimmed)) {
            return '';
        }

        const count = Number(trimmed);

        if (!Number.isInteger(count) || count < 5 || count > 24) {
            return '';
        }

        return String(count);
    },
    set(value: string) {
        if (value === '') {
            return;
        }

        const count = Number(value);

        if (!Number.isInteger(count) || count < 5 || count > 24) {
            return;
        }

        form.schedule.times_per_day = String(count);
    },
});
</script>

<template>
    <div>
        <Label
            :id="`${idPrefix}-schedule-times-per-day-label`"
            :for="`${idPrefix}-schedule-times-per-day-custom`"
            :class="cn(patientFormLabelClass, 'text-xl')"
        >
            {{ t('patient.medications.fields.timesPerDay') }}
        </Label>
        <div
            :id="`${idPrefix}-schedule-times-per-day`"
            :class="
                cn(
                    'mt-2 space-y-4 touch-manipulation',
                    form.errors['schedule.times_per_day']
                        ? 'rounded-2xl p-0.5 ring-2 ring-danger/25'
                        : null,
                )
            "
        >
            <div
                class="grid grid-cols-1 gap-2 sm:grid-cols-2 sm:gap-3"
                role="radiogroup"
                :aria-labelledby="`${idPrefix}-schedule-times-per-day-label`"
                :aria-invalid="Boolean(form.errors['schedule.times_per_day'])"
                :aria-describedby="
                    form.errors['schedule.times_per_day']
                        ? `${idPrefix}-schedule-times-per-day-error`
                        : undefined
                "
            >
                <button
                    v-for="value in TIMES_PER_DAY_PRESET_VALUES"
                    :id="`${idPrefix}-schedule-times-per-day-option-${value}`"
                    :key="value"
                    type="button"
                    class="min-h-14 rounded-2xl border-2 px-4 py-3.5 text-left text-base font-semibold leading-snug transition-colors focus-visible:border-focus focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus/30 md:min-h-[3.75rem] md:px-5 md:text-lg"
                    :class="
                        !showCustomTimesPerDaySelect &&
                            form.schedule.times_per_day.trim() === value
                            ? 'border-primary bg-primary/10 text-text-heading'
                            : 'border-border bg-surface text-text hover:bg-surface-hover'
                    "
                    :aria-pressed="
                        !showCustomTimesPerDaySelect &&
                            form.schedule.times_per_day.trim() === value
                    "
                    @click="selectTimesPerDayPreset(value)"
                >
                    {{ timesPerDayOptionLabel(Number(value)) }}
                </button>
                <button
                    :id="`${idPrefix}-schedule-times-per-day-custom-trigger`"
                    type="button"
                    class="min-h-14 rounded-2xl border-2 px-4 py-3.5 text-left text-base font-semibold leading-snug transition-colors focus-visible:border-focus focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus/30 md:min-h-[3.75rem] md:px-5 md:text-lg"
                    :class="
                        showCustomTimesPerDaySelect
                            ? 'border-primary bg-primary/10 text-text-heading'
                            : 'border-border bg-surface text-text hover:bg-surface-hover'
                    "
                    :aria-pressed="showCustomTimesPerDaySelect"
                    @click="selectCustomTimesPerDay"
                >
                    {{ t('patient.medications.intakePeriodPresets.custom') }}
                </button>
            </div>
            <select
                v-if="showCustomTimesPerDaySelect"
                :id="`${idPrefix}-schedule-times-per-day-custom`"
                v-model="timesPerDayCustomSelect"
                class="w-full text-base md:text-lg"
                :aria-label="t('patient.medications.timesPerDay.customSelectAriaLabel')"
                :class="
                    cn(
                        patientFormSelectBaseClass,
                        form.errors['schedule.times_per_day']
                            ? patientFormFieldInvalidClass
                            : null,
                    )
                "
                :style="patientFormSelectChevronStyle"
                :aria-invalid="Boolean(form.errors['schedule.times_per_day'])"
                :aria-describedby="
                    form.errors['schedule.times_per_day']
                        ? `${idPrefix}-schedule-times-per-day-error`
                        : undefined
                "
            >
                <option disabled value="">
                    {{ t('patient.medications.timesPerDay.customPlaceholder') }}
                </option>
                <option
                    v-for="count in TIMES_PER_DAY_CUSTOM_OPTIONS"
                    :key="count"
                    :value="String(count)"
                >
                    {{ timesPerDayOptionLabel(count) }}
                </option>
            </select>
        </div>
        <InputError
            :id="`${idPrefix}-schedule-times-per-day-error`"
            :message="form.errors['schedule.times_per_day']"
        />
    </div>
</template>
