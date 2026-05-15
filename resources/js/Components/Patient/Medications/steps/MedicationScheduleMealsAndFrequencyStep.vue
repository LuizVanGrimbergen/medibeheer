<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import type { MedicationCreateFormWithErrors } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { InputError } from '@/Components/ui/input-error';
import { MEDICATION_MEAL_TIMING_OPTIONS } from '@/lib/patient/medications/options/medicationMealTimingOptions';
import {
    patientFormFieldInvalidClass,
    patientFormLabelClass,
    patientFormSelectBaseClass,
    patientFormSelectChevronStyle,
} from '@/lib/patient/patientFormFieldClasses';
import type { MedicationIntakeFrequencyValue } from '@/lib/types';
import { cn } from '@/lib/utils';

const INTAKE_FREQUENCY_PRESETS: readonly MedicationIntakeFrequencyValue[] = [
    'daily',
    'every_2_days',
    'every_3_days',
];

function isIntakeFrequencyCustomInterval(frequency: string): boolean {
    const match = /^every_(\d+)_days$/.exec(frequency);

    if (match === null) {
        return false;
    }

    const dayCount = Number(match[1]);

    return Number.isInteger(dayCount) && dayCount >= 5 && dayCount <= 60;
}

function isIntakeFrequencyPreset(frequency: string): boolean {
    return (INTAKE_FREQUENCY_PRESETS as readonly string[]).includes(frequency);
}

const INTAKE_FREQUENCY_CUSTOM_DAY_OPTIONS = Array.from({ length: 56 }, (_, index) => index + 5);

const ISO_WEEKDAY_NUMBERS = [1, 2, 3, 4, 5, 6, 7] as const;

const { form, idPrefix } = defineProps<{
    form: MedicationCreateFormWithErrors;
    idPrefix: string;
}>();

const { t } = useI18n();

const hasIntakeFrequencyBlockError = computed(
    () =>
        Boolean(form.errors['schedule.intake_frequency']) ||
        Boolean(form.errors['schedule.intake_weekdays']),
);

const prefersCustomIntakeFrequency = ref(
    isIntakeFrequencyCustomInterval(form.schedule.intake_frequency),
);

const showCustomIntakeFrequencySelect = computed(
    () =>
        prefersCustomIntakeFrequency.value ||
        isIntakeFrequencyCustomInterval(form.schedule.intake_frequency),
);

function setIntakeFrequencyPreset(frequency: MedicationIntakeFrequencyValue): void {
    prefersCustomIntakeFrequency.value = false;
    form.schedule.intake_frequency = frequency;

    if (frequency !== 'weekdays') {
        form.schedule.intake_weekdays = [];
    }
}

function setIntakeFrequencyWeekdays(): void {
    prefersCustomIntakeFrequency.value = false;
    form.schedule.intake_frequency = 'weekdays';
}

function selectCustomIntakeFrequency(): void {
    prefersCustomIntakeFrequency.value = true;
    form.schedule.intake_weekdays = [];
}

watch(
    () => form.schedule.intake_frequency,
    (frequency) => {
        if (prefersCustomIntakeFrequency.value || frequency === 'weekdays') {
            return;
        }

        if (!isIntakeFrequencyPreset(frequency) && frequency.length > 0) {
            prefersCustomIntakeFrequency.value = true;
        }
    },
);

function toggleIsoWeekday(day: (typeof ISO_WEEKDAY_NUMBERS)[number]): void {
    if (form.schedule.intake_frequency !== 'weekdays') {
        return;
    }

    const selected = new Set(form.schedule.intake_weekdays);

    if (selected.has(day)) {
        selected.delete(day);
    } else {
        selected.add(day);
    }

    form.schedule.intake_weekdays = Array.from(selected).sort((a, b) => a - b);
}

function presetIntakeFrequencyLabel(frequency: MedicationIntakeFrequencyValue): string {
    if (frequency === 'daily') {
        return t('patient.medications.intakeFrequencies.daily');
    }

    const match = /^every_(\d+)_days$/.exec(frequency);

    if (!match) {
        return frequency;
    }

    const dayCount = Number(match[1]);

    if (!Number.isInteger(dayCount) || dayCount < 2 || dayCount > 60) {
        return frequency;
    }

    return t('patient.medications.intakeFrequencies.everyNDays', { n: dayCount });
}

const intakeFrequencyCustomDaysSelect = computed({
    get(): string {
        const match = /^every_(\d+)_days$/.exec(form.schedule.intake_frequency);

        if (!match) {
            return '';
        }

        const dayCount = Number(match[1]);

        if (!Number.isInteger(dayCount) || dayCount < 5 || dayCount > 60) {
            return '';
        }

        return String(dayCount);
    },
    set(value: string) {
        if (value === '') {
            return;
        }

        const dayCount = Number(value);

        if (!Number.isInteger(dayCount) || dayCount < 5 || dayCount > 60) {
            return;
        }

        form.schedule.intake_frequency =
            `every_${dayCount}_days` as MedicationIntakeFrequencyValue;
        form.schedule.intake_weekdays = [];
    },
});
</script>

<template>
    <div class="space-y-8">
        <div>
            <p
                :id="`${idPrefix}-schedule-meal-timing-label`"
                :class="cn(patientFormLabelClass, 'text-xl')"
            >
                {{ t('patient.medications.fields.mealTiming') }}
            </p>
            <div
                :id="`${idPrefix}-schedule-meal-timing`"
                :class="
                    cn(
                        'mt-2 grid grid-cols-2 gap-x-2 gap-y-3 touch-manipulation md:grid-cols-4 md:gap-x-4 md:gap-y-4',
                        form.errors['schedule.meal_timing']
                            ? 'rounded-2xl p-0.5 ring-2 ring-danger/25'
                            : null,
                    )
                "
                role="radiogroup"
                :aria-labelledby="`${idPrefix}-schedule-meal-timing-label`"
                :aria-invalid="Boolean(form.errors['schedule.meal_timing'])"
                :aria-describedby="
                    form.errors['schedule.meal_timing']
                        ? `${idPrefix}-schedule-meal-timing-error`
                        : undefined
                "
            >
                <div
                    v-for="option in MEDICATION_MEAL_TIMING_OPTIONS"
                    :key="option.timing"
                    class="flex w-full flex-col items-center gap-1 md:gap-2"
                >
                    <div class="relative mx-auto w-full max-w-24 md:max-w-28">
                        <div
                            class="pointer-events-none pb-[100%]"
                            aria-hidden="true"
                        />
                        <button
                            :id="`${idPrefix}-schedule-meal-timing-option-${option.timing}`"
                            type="button"
                            class="absolute inset-0 flex items-center justify-center rounded-xl border-2 p-2 transition-colors focus-visible:border-focus focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus/30 md:rounded-2xl md:border-[3px] md:p-3 md:focus-visible:ring-4"
                            :class="
                                cn(
                                    form.schedule.meal_timing === option.timing
                                        ? 'border-primary bg-primary/10 text-text-heading'
                                        : 'border-border bg-surface text-text hover:bg-surface-hover',
                                )
                            "
                            :aria-pressed="form.schedule.meal_timing === option.timing"
                            :aria-labelledby="`${idPrefix}-schedule-meal-timing-option-${option.timing}-label`"
                            @click="form.schedule.meal_timing = option.timing"
                        >
                            <img
                                v-if="option.visual.kind === 'image'"
                                :src="option.visual.src"
                                alt=""
                                class="size-16 shrink-0 object-contain md:size-20"
                                aria-hidden="true"
                            />
                            <component
                                :is="option.visual.icon"
                                v-else
                                :color="
                                    form.schedule.meal_timing === option.timing
                                        ? 'var(--color-primary)'
                                        : 'var(--color-text-muted)'
                                "
                                class="size-11 shrink-0 md:size-14"
                                aria-hidden="true"
                            />
                        </button>
                    </div>
                    <span
                        :id="`${idPrefix}-schedule-meal-timing-option-${option.timing}-label`"
                        class="flex min-h-10 max-w-full cursor-pointer select-none items-center justify-center wrap-break-word hyphens-auto px-0.5 text-center font-body text-sm font-bold leading-snug touch-manipulation md:max-w-27 md:text-base md:leading-snug"
                        :class="
                            form.schedule.meal_timing === option.timing
                                ? 'text-text-heading'
                                : 'text-text'
                        "
                        @click="form.schedule.meal_timing = option.timing"
                    >
                        {{ t(`patient.medications.mealTimings.${option.timing}`) }}
                    </span>
                </div>
            </div>
            <InputError
                :id="`${idPrefix}-schedule-meal-timing-error`"
                :message="form.errors['schedule.meal_timing']"
            />
        </div>

        <div>
            <p
                :id="`${idPrefix}-schedule-intake-frequency-label`"
                :class="cn(patientFormLabelClass, 'text-xl')"
            >
                {{ t('patient.medications.fields.intakeFrequency') }}
            </p>
            <div
                :id="`${idPrefix}-schedule-intake-frequency`"
                :class="
                    cn(
                        'mt-2 space-y-4 touch-manipulation',
                        hasIntakeFrequencyBlockError
                            ? 'rounded-2xl p-0.5 ring-2 ring-danger/25'
                            : null,
                    )
                "
            >
                <div
                    class="grid grid-cols-1 gap-2 sm:grid-cols-2 sm:gap-3"
                    role="radiogroup"
                    :aria-labelledby="`${idPrefix}-schedule-intake-frequency-label`"
                    :aria-invalid="hasIntakeFrequencyBlockError"
                    :aria-describedby="
                        [
                            form.errors['schedule.intake_frequency']
                                ? `${idPrefix}-schedule-intake-frequency-error`
                                : null,
                            form.errors['schedule.intake_weekdays']
                                ? `${idPrefix}-schedule-intake-weekdays-error`
                                : null,
                        ]
                            .filter(Boolean)
                            .join(' ') || undefined
                    "
                >
                    <button
                        v-for="frequency in INTAKE_FREQUENCY_PRESETS"
                        :id="`${idPrefix}-schedule-intake-frequency-option-${frequency}`"
                        :key="frequency"
                        type="button"
                        class="min-h-14 rounded-2xl border-2 px-4 py-3.5 text-left text-base font-semibold leading-snug transition-colors focus-visible:border-focus focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus/30 md:min-h-[3.75rem] md:px-5 md:text-lg"
                        :class="
                            !showCustomIntakeFrequencySelect &&
                                form.schedule.intake_frequency === frequency
                                ? 'border-primary bg-primary/10 text-text-heading'
                                : 'border-border bg-surface text-text hover:bg-surface-hover'
                        "
                        :aria-pressed="
                            !showCustomIntakeFrequencySelect &&
                                form.schedule.intake_frequency === frequency
                        "
                        @click="setIntakeFrequencyPreset(frequency)"
                    >
                        {{ presetIntakeFrequencyLabel(frequency) }}
                    </button>
                    <button
                        :id="`${idPrefix}-schedule-intake-frequency-custom`"
                        type="button"
                        class="min-h-14 rounded-2xl border-2 px-4 py-3.5 text-left text-base font-semibold leading-snug transition-colors focus-visible:border-focus focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus/30 md:min-h-[3.75rem] md:px-5 md:text-lg"
                        :class="
                            showCustomIntakeFrequencySelect
                                ? 'border-primary bg-primary/10 text-text-heading'
                                : 'border-border bg-surface text-text hover:bg-surface-hover'
                        "
                        :aria-pressed="showCustomIntakeFrequencySelect"
                        @click="selectCustomIntakeFrequency"
                    >
                        {{ t('patient.medications.intakePeriodPresets.custom') }}
                    </button>
                </div>
                <select
                    v-if="showCustomIntakeFrequencySelect"
                    :id="`${idPrefix}-schedule-intake-frequency-custom-days`"
                    v-model="intakeFrequencyCustomDaysSelect"
                    class="w-full text-base md:text-lg"
                    :aria-label="
                        t('patient.medications.intakeFrequencies.customIntervalSelectAriaLabel')
                    "
                    :class="
                        cn(
                            patientFormSelectBaseClass,
                            hasIntakeFrequencyBlockError
                                ? patientFormFieldInvalidClass
                                : null,
                        )
                    "
                    :style="patientFormSelectChevronStyle"
                    :aria-invalid="hasIntakeFrequencyBlockError"
                    :aria-describedby="
                        [
                            form.errors['schedule.intake_frequency']
                                ? `${idPrefix}-schedule-intake-frequency-error`
                                : null,
                            form.errors['schedule.intake_weekdays']
                                ? `${idPrefix}-schedule-intake-weekdays-error`
                                : null,
                        ]
                            .filter(Boolean)
                            .join(' ') || undefined
                    "
                >
                    <option disabled value="">
                        {{ t('patient.medications.intakeFrequencies.customIntervalPlaceholder') }}
                    </option>
                    <option
                        v-for="dayCount in INTAKE_FREQUENCY_CUSTOM_DAY_OPTIONS"
                        :key="dayCount"
                        :value="String(dayCount)"
                    >
                        {{
                            t('patient.medications.intakeFrequencies.everyNDays', {
                                n: dayCount,
                            })
                        }}
                    </option>
                </select>
                <button
                    :id="`${idPrefix}-schedule-intake-frequency-option-weekdays`"
                    type="button"
                    class="w-full min-h-14 rounded-2xl border-2 px-4 py-3.5 text-left text-base font-semibold leading-snug transition-colors focus-visible:border-focus focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus/30 touch-manipulation md:min-h-[3.75rem] md:px-5 md:text-lg"
                    :class="
                        form.schedule.intake_frequency === 'weekdays'
                            ? 'border-primary bg-primary/10 text-text-heading'
                            : 'border-border bg-surface text-text hover:bg-surface-hover'
                    "
                    :aria-pressed="form.schedule.intake_frequency === 'weekdays'"
                    @click="setIntakeFrequencyWeekdays()"
                >
                    {{ t('patient.medications.intakeFrequencies.weekdaysButton') }}
                </button>
                <fieldset
                    v-if="form.schedule.intake_frequency === 'weekdays'"
                    :id="`${idPrefix}-schedule-intake-weekdays`"
                    class="m-0 min-w-0 space-y-2 border-0 p-0"
                    :aria-invalid="Boolean(form.errors['schedule.intake_weekdays'])"
                    :aria-describedby="
                        form.errors['schedule.intake_weekdays']
                            ? `${idPrefix}-schedule-intake-weekdays-error`
                            : undefined
                    "
                >
                    <legend
                        :id="`${idPrefix}-schedule-intake-weekdays-label`"
                        class="float-none w-full px-0 text-sm font-semibold leading-snug text-text-heading md:text-base"
                    >
                        {{ t('patient.medications.fields.intakeWeekdays') }}
                    </legend>
                    <div class="flex flex-wrap gap-2 touch-manipulation">
                        <button
                            v-for="day in ISO_WEEKDAY_NUMBERS"
                            :id="`${idPrefix}-schedule-intake-weekday-${day}`"
                            :key="day"
                            type="button"
                            class="min-h-12 min-w-[3.25rem] rounded-2xl border-2 px-3 text-base font-bold leading-none transition-colors focus-visible:border-focus focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus/30 touch-manipulation md:min-h-14 md:min-w-16 md:text-lg"
                            :class="
                                form.schedule.intake_weekdays.includes(day)
                                    ? 'border-primary bg-primary/10 text-text-heading'
                                    : 'border-border bg-surface text-text hover:bg-surface-hover'
                            "
                            :aria-pressed="form.schedule.intake_weekdays.includes(day)"
                            @click="toggleIsoWeekday(day)"
                        >
                            {{ t(`patient.medications.weekdayIso.${day}`) }}
                        </button>
                    </div>
                </fieldset>
            </div>
            <InputError
                :id="`${idPrefix}-schedule-intake-frequency-error`"
                :message="form.errors['schedule.intake_frequency']"
            />
            <InputError
                :id="`${idPrefix}-schedule-intake-weekdays-error`"
                :message="form.errors['schedule.intake_weekdays']"
            />
        </div>
    </div>
</template>
