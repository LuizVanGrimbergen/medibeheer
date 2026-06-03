<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import type { MedicationCreateFormWithErrors } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { Card, CardContent } from '@/Components/ui/card';
import { InputError } from '@/Components/ui/input-error';
import {
    isCustomIntakeFrequencyInterval,
    isWizardIntakeFrequencyPreset,
    MEDICATION_INTAKE_FREQUENCY_CUSTOM_DAY_MAX,
    MEDICATION_INTAKE_FREQUENCY_CUSTOM_DAY_MIN,
    MEDICATION_INTAKE_FREQUENCY_CUSTOM_DAY_OPTIONS,
    MEDICATION_INTAKE_FREQUENCY_WIZARD_PRESETS,
    medicationIntakeFrequencyLabel,
    parseEveryNDaysFrequency,
} from '@/lib/patient/medications/schedule/medicationIntakeFrequencyDisplay';
import {
    patientFormFieldInvalidClass,
    patientFormLabelClass,
    patientFormSelectBaseClass,
    patientFormSelectChevronStyle,
} from '@/lib/patient/patientFormFieldClasses';
import type { MedicationIntakeFrequencyValue } from '@/lib/types';
import { MEDICATION_MEAL_TIMING_VALUES } from '@/lib/types';
import { cn } from '@/lib/utils';

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
    isCustomIntakeFrequencyInterval(form.schedule.intake_frequency),
);

const showCustomIntakeFrequencySelect = computed(
    () =>
        prefersCustomIntakeFrequency.value ||
        isCustomIntakeFrequencyInterval(form.schedule.intake_frequency),
);

function setIntakeFrequencyPreset(
    frequency: MedicationIntakeFrequencyValue,
): void {
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

        if (!isWizardIntakeFrequencyPreset(frequency) && frequency.length > 0) {
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

const intakeFrequencyCustomDaysSelect = computed({
    get(): string {
        const dayCount = parseEveryNDaysFrequency(
            form.schedule.intake_frequency,
        );

        if (
            dayCount === null ||
            dayCount < MEDICATION_INTAKE_FREQUENCY_CUSTOM_DAY_MIN ||
            dayCount > MEDICATION_INTAKE_FREQUENCY_CUSTOM_DAY_MAX
        ) {
            return '';
        }

        return String(dayCount);
    },
    set(value: string) {
        if (value === '') {
            return;
        }

        const dayCount = Number(value);

        if (
            !Number.isInteger(dayCount) ||
            dayCount < MEDICATION_INTAKE_FREQUENCY_CUSTOM_DAY_MIN ||
            dayCount > MEDICATION_INTAKE_FREQUENCY_CUSTOM_DAY_MAX
        ) {
            return;
        }

        form.schedule.intake_frequency =
            `every_${dayCount}_days` as MedicationIntakeFrequencyValue;
        form.schedule.intake_weekdays = [];
    },
});
</script>

<template>
    <div class="space-y-3 md:space-y-3">
        <Card
            class="border-border/80 bg-surface text-text rounded-2xl border shadow-md shadow-black/[0.04] md:rounded-3xl"
        >
            <CardContent class="p-0">
                <div
                    class="bg-surface space-y-5 rounded-2xl px-4 py-4 md:space-y-5 md:rounded-3xl md:px-5 md:py-5 lg:space-y-6 lg:px-7 lg:py-7"
                >
                    <div>
                        <p
                            :id="`${idPrefix}-schedule-meal-timing-label`"
                            :class="cn(patientFormLabelClass, 'text-xl')"
                        >
                            {{ t('patient.medications.fields.mealTiming') }}
                            <span class="text-danger">*</span>
                        </p>
                        <div
                            :id="`${idPrefix}-schedule-meal-timing`"
                            :class="
                                cn(
                                    'mt-2 touch-manipulation',
                                    form.errors['schedule.meal_timing']
                                        ? 'ring-danger/25 rounded-2xl p-0.5 ring-2'
                                        : null,
                                )
                            "
                        >
                            <div
                                class="grid grid-cols-1 gap-2 sm:grid-cols-2 sm:gap-3"
                                role="radiogroup"
                                :aria-labelledby="`${idPrefix}-schedule-meal-timing-label`"
                                :aria-invalid="
                                    Boolean(form.errors['schedule.meal_timing'])
                                "
                                :aria-describedby="
                                    form.errors['schedule.meal_timing']
                                        ? `${idPrefix}-schedule-meal-timing-error`
                                        : undefined
                                "
                            >
                                <button
                                    v-for="timing in MEDICATION_MEAL_TIMING_VALUES"
                                    :id="`${idPrefix}-schedule-meal-timing-option-${timing}`"
                                    :key="timing"
                                    type="button"
                                    class="focus-visible:border-focus focus-visible:ring-focus/30 min-h-14 rounded-2xl border-2 px-4 py-3.5 text-left text-base leading-snug font-semibold transition-colors focus-visible:ring-2 focus-visible:outline-none md:min-h-[3.75rem] md:px-5 md:text-lg"
                                    :class="
                                        form.schedule.meal_timing === timing
                                            ? 'border-primary bg-primary/10 text-text-heading'
                                            : 'border-border bg-surface text-text hover:bg-surface-hover'
                                    "
                                    :aria-pressed="
                                        form.schedule.meal_timing === timing
                                    "
                                    @click="form.schedule.meal_timing = timing"
                                >
                                    {{
                                        t(
                                            `patient.medications.mealTimings.${timing}`,
                                        )
                                    }}
                                </button>
                            </div>
                        </div>
                        <InputError
                            :id="`${idPrefix}-schedule-meal-timing-error`"
                            :message="form.errors['schedule.meal_timing']"
                        />
                    </div>
                </div>
            </CardContent>
        </Card>

        <Card
            class="border-border/80 bg-surface text-text rounded-2xl border shadow-md shadow-black/[0.04] md:rounded-3xl"
        >
            <CardContent class="p-0">
                <div
                    class="bg-surface space-y-5 rounded-2xl px-4 py-4 md:space-y-5 md:rounded-3xl md:px-5 md:py-5 lg:space-y-6 lg:px-7 lg:py-7"
                >
                    <div>
                        <p
                            :id="`${idPrefix}-schedule-intake-frequency-label`"
                            :class="cn(patientFormLabelClass, 'text-xl')"
                        >
                            {{
                                t('patient.medications.fields.intakeFrequency')
                            }}
                            <span class="text-danger">*</span>
                        </p>
                        <div
                            :id="`${idPrefix}-schedule-intake-frequency`"
                            :class="
                                cn(
                                    'mt-2 touch-manipulation space-y-4',
                                    hasIntakeFrequencyBlockError
                                        ? 'ring-danger/25 rounded-2xl p-0.5 ring-2'
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
                                    v-for="frequency in MEDICATION_INTAKE_FREQUENCY_WIZARD_PRESETS"
                                    :id="`${idPrefix}-schedule-intake-frequency-option-${frequency}`"
                                    :key="frequency"
                                    type="button"
                                    class="focus-visible:border-focus focus-visible:ring-focus/30 min-h-14 rounded-2xl border-2 px-4 py-3.5 text-left text-base leading-snug font-semibold transition-colors focus-visible:ring-2 focus-visible:outline-none md:min-h-[3.75rem] md:px-5 md:text-lg"
                                    :class="
                                        !showCustomIntakeFrequencySelect &&
                                        form.schedule.intake_frequency ===
                                            frequency
                                            ? 'border-primary bg-primary/10 text-text-heading'
                                            : 'border-border bg-surface text-text hover:bg-surface-hover'
                                    "
                                    :aria-pressed="
                                        !showCustomIntakeFrequencySelect &&
                                        form.schedule.intake_frequency ===
                                            frequency
                                    "
                                    @click="setIntakeFrequencyPreset(frequency)"
                                >
                                    {{
                                        medicationIntakeFrequencyLabel(
                                            t,
                                            frequency,
                                        )
                                    }}
                                </button>
                                <button
                                    :id="`${idPrefix}-schedule-intake-frequency-custom`"
                                    type="button"
                                    class="focus-visible:border-focus focus-visible:ring-focus/30 min-h-14 rounded-2xl border-2 px-4 py-3.5 text-left text-base leading-snug font-semibold transition-colors focus-visible:ring-2 focus-visible:outline-none md:min-h-[3.75rem] md:px-5 md:text-lg"
                                    :class="
                                        showCustomIntakeFrequencySelect
                                            ? 'border-primary bg-primary/10 text-text-heading'
                                            : 'border-border bg-surface text-text hover:bg-surface-hover'
                                    "
                                    :aria-pressed="
                                        showCustomIntakeFrequencySelect
                                    "
                                    @click="selectCustomIntakeFrequency"
                                >
                                    {{
                                        t(
                                            'patient.medications.intakePeriodPresets.custom',
                                        )
                                    }}
                                </button>
                            </div>
                            <select
                                v-if="showCustomIntakeFrequencySelect"
                                :id="`${idPrefix}-schedule-intake-frequency-custom-days`"
                                v-model="intakeFrequencyCustomDaysSelect"
                                class="w-full text-base md:text-lg"
                                :aria-label="
                                    t(
                                        'patient.medications.intakeFrequencies.customIntervalSelectAriaLabel',
                                    )
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
                                    {{
                                        t(
                                            'patient.medications.intakeFrequencies.customIntervalPlaceholder',
                                        )
                                    }}
                                </option>
                                <option
                                    v-for="dayCount in MEDICATION_INTAKE_FREQUENCY_CUSTOM_DAY_OPTIONS"
                                    :key="dayCount"
                                    :value="String(dayCount)"
                                >
                                    {{
                                        t(
                                            'patient.medications.intakeFrequencies.everyNDays',
                                            {
                                                n: dayCount,
                                            },
                                        )
                                    }}
                                </option>
                            </select>
                            <button
                                :id="`${idPrefix}-schedule-intake-frequency-option-weekdays`"
                                type="button"
                                class="focus-visible:border-focus focus-visible:ring-focus/30 min-h-14 w-full touch-manipulation rounded-2xl border-2 px-4 py-3.5 text-left text-base leading-snug font-semibold transition-colors focus-visible:ring-2 focus-visible:outline-none md:min-h-[3.75rem] md:px-5 md:text-lg"
                                :class="
                                    form.schedule.intake_frequency ===
                                    'weekdays'
                                        ? 'border-primary bg-primary/10 text-text-heading'
                                        : 'border-border bg-surface text-text hover:bg-surface-hover'
                                "
                                :aria-pressed="
                                    form.schedule.intake_frequency ===
                                    'weekdays'
                                "
                                @click="setIntakeFrequencyWeekdays()"
                            >
                                {{
                                    t(
                                        'patient.medications.intakeFrequencies.weekdaysButton',
                                    )
                                }}
                            </button>
                            <fieldset
                                v-if="
                                    form.schedule.intake_frequency ===
                                    'weekdays'
                                "
                                :id="`${idPrefix}-schedule-intake-weekdays`"
                                class="m-0 min-w-0 space-y-2 border-0 p-0"
                                :aria-invalid="
                                    Boolean(
                                        form.errors['schedule.intake_weekdays'],
                                    )
                                "
                                :aria-describedby="
                                    form.errors['schedule.intake_weekdays']
                                        ? `${idPrefix}-schedule-intake-weekdays-error`
                                        : undefined
                                "
                            >
                                <legend
                                    :id="`${idPrefix}-schedule-intake-weekdays-label`"
                                    class="text-text-heading float-none w-full px-0 text-sm leading-snug font-semibold md:text-base"
                                >
                                    {{
                                        t(
                                            'patient.medications.fields.intakeWeekdays',
                                        )
                                    }}
                                </legend>
                                <div
                                    class="flex touch-manipulation flex-wrap gap-2"
                                >
                                    <button
                                        v-for="day in ISO_WEEKDAY_NUMBERS"
                                        :id="`${idPrefix}-schedule-intake-weekday-${day}`"
                                        :key="day"
                                        type="button"
                                        class="focus-visible:border-focus focus-visible:ring-focus/30 min-h-12 min-w-[3.25rem] touch-manipulation rounded-2xl border-2 px-3 text-base leading-none font-bold transition-colors focus-visible:ring-2 focus-visible:outline-none md:min-h-14 md:min-w-16 md:text-lg"
                                        :class="
                                            form.schedule.intake_weekdays.includes(
                                                day,
                                            )
                                                ? 'border-primary bg-primary/10 text-text-heading'
                                                : 'border-border bg-surface text-text hover:bg-surface-hover'
                                        "
                                        :aria-pressed="
                                            form.schedule.intake_weekdays.includes(
                                                day,
                                            )
                                        "
                                        @click="toggleIsoWeekday(day)"
                                    >
                                        {{
                                            t(
                                                `patient.medications.weekdayIso.${day}`,
                                            )
                                        }}
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
            </CardContent>
        </Card>
    </div>
</template>
