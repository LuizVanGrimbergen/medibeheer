<script setup lang="ts">
import { Pencil } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import type {
    MedicationCreateFormWithErrors,
    MedicationFormWizardStep,
} from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { IconActionButton } from '@/Components/ui/icon-action-button';
import { medicationDoseUnitChipForAmount } from '@/lib/patient/medications/options/medicationDoseUnitChipForAmount';
import { medicationStrengthDisplayValue } from '@/lib/patient/medications/strength/medicationStrengthDisplayValue';
import { formatMedicationStockDisplayAmount } from '@/lib/patient/medications/stock/formatMedicationStockDisplayAmount';
import { medicationStockDisplayDoseUnit } from '@/lib/patient/medications/stock/medicationStockDisplayDoseUnit';
import { parseMedicationTimesPerDayCount } from '@/lib/patient/medications/validation/medicationFormValidationPrimitives';
import { patientFormLabelClass } from '@/lib/patient/patientFormFieldClasses';
import type {
    MedicationIntakeFrequencyValue,
    MedicationMealTimingValue,
    MedicationTypeValue,
} from '@/lib/types';
import { MEDICATION_MEAL_TIMING_VALUES } from '@/lib/types';
import { cn } from '@/lib/utils';

const INTAKE_FREQUENCY_UI_PRESETS = [
    'daily',
    'every_2_days',
    'every_3_days',
    'every_4_days',
] as const satisfies readonly MedicationIntakeFrequencyValue[];

const props = withDefaults(
    defineProps<{
        form: MedicationCreateFormWithErrors;
        idPrefix: string;
        goToWizardStep: (step: MedicationFormWizardStep, focusElementIdSuffix?: string) => void;
        showStepNavigation?: boolean;
        alwaysShowNoteSummaryRow?: boolean;
    }>(),
    {
        showStepNavigation: true,
        alwaysShowNoteSummaryRow: false,
    },
);

const { t } = useI18n();

const summaryRowGroupClass =
    'flex flex-col gap-0.5 border-b border-border/60 py-3 last:border-b-0 sm:flex-row sm:items-baseline sm:justify-between sm:gap-4 sm:py-3.5';

const summaryDdClass =
    'flex min-w-0 flex-1 flex-row items-baseline justify-end gap-2';

function activateSummaryRow(step: MedicationFormWizardStep, focusElementIdSuffix: string): void {
    if (props.form.processing || !props.showStepNavigation) {
        return;
    }

    props.goToWizardStep(step, focusElementIdSuffix);
}

const summaryLabelClass = 'shrink-0 text-sm font-medium text-text-muted md:text-base';
const summaryValueClass = 'min-w-0 flex-1 text-base font-semibold leading-snug text-text-heading sm:text-end md:text-lg';

function intakeFrequencyPresetLabel(value: MedicationIntakeFrequencyValue): string {
    if (value === 'daily') {
        return t('patient.medications.intakeFrequencies.daily');
    }

    if (value === 'weekdays') {
        return t('patient.medications.intakeFrequencies.weekdays');
    }

    const match = /^every_(\d+)_days$/.exec(value);

    if (match === null) {
        return value;
    }

    const dayCount = Number(match[1]);

    if (!Number.isInteger(dayCount) || dayCount < 2 || dayCount > 60) {
        return value;
    }

    return t('patient.medications.intakeFrequencies.everyNDays', { n: dayCount });
}

const intakeFrequencySummary = computed(() => {
    const value = props.form.schedule.intake_frequency as MedicationIntakeFrequencyValue | '';

    if (value === '') {
        return '—';
    }

    if (value === 'weekdays') {
        const days = props.form.schedule.intake_weekdays;

        if (days.length < 1) {
            return intakeFrequencyPresetLabel(value);
        }

        const parts = days.map((d) => t(`patient.medications.weekdayIso.${d}`));

        return `${intakeFrequencyPresetLabel(value)} (${parts.join(', ')})`;
    }

    return intakeFrequencyPresetLabel(value);
});

const mealTimingLabel = computed(() => {
    const value = props.form.schedule.meal_timing as MedicationMealTimingValue | '';

    if (value === '') {
        return '—';
    }

    return t(`patient.medications.mealTimings.${value}`);
});

const typeLabel = computed(() => {
    const value = props.form.type_medication as MedicationTypeValue | '';

    if (value === '') {
        return '—';
    }

    return t(`patient.medications.types.${value}`);
});

const doseTimesJoined = computed(() => {
    const count = parseMedicationTimesPerDayCount(props.form.schedule.times_per_day);

    if (count === null) {
        return '—';
    }

    return props.form.schedule.dose_time_slots
        .slice(0, count)
        .map((slot) => slot.trim())
        .filter((slot) => slot.length > 0)
        .join(', ');
});

const doseUnitForStock = computed(() =>
    medicationStockDisplayDoseUnit(props.form.dose_unit, props.form.strength_unit),
);

const currentStockSummary = computed(() =>
    formatMedicationStockDisplayAmount(t, props.form.current_stock, doseUnitForStock.value),
);

const summaryMealTimingFocusSuffix = computed(() => {
    const v = props.form.schedule.meal_timing;
    const timing = v === '' ? MEDICATION_MEAL_TIMING_VALUES[0] : v;

    return `schedule-meal-timing-option-${timing}`;
});

const summaryIntakeFrequencyFocusSuffix = computed(() => {
    const v = props.form.schedule.intake_frequency;

    if (v === 'weekdays') {
        if (props.form.schedule.intake_weekdays.length < 1) {
            return 'schedule-intake-frequency-option-weekdays';
        }

        return `schedule-intake-weekday-${props.form.schedule.intake_weekdays[0]}`;
    }

    if ((INTAKE_FREQUENCY_UI_PRESETS as readonly string[]).includes(v)) {
        return `schedule-intake-frequency-option-${v}`;
    }

    const match = /^every_(\d+)_days$/.exec(v);

    if (match !== null) {
        const n = Number(match[1]);

        if (Number.isInteger(n) && n >= 5 && n <= 60) {
            return 'schedule-intake-frequency-custom-days';
        }
    }

    return 'schedule-intake-frequency-option-daily';
});

const summaryTimesPerDayFocusSuffix = computed(() => {
    const trimmed = props.form.schedule.times_per_day.trim();

    if (/^[1-4]$/.test(trimmed)) {
        return `schedule-times-per-day-option-${trimmed}`;
    }

    return 'schedule-times-per-day-custom';
});

const summaryTypeFocusSuffix = computed(() => {
    const v = props.form.type_medication;

    if (v === '') {
        return 'type';
    }

    return `type-option-${v}`;
});

const overviewSectionHeadingClass = cn(
    patientFormLabelClass,
    'mb-3 text-lg text-text-heading md:mb-4 md:text-xl',
);

function summaryRowAria(fieldTranslationKey: string): string {
    return t('patient.medications.overview.editRowAria', {
        field: t(`patient.medications.${fieldTranslationKey}`),
    });
}
</script>

<template>
    <div class="space-y-6">
        <h2
            :id="`${idPrefix}-create-summary-title`"
            tabindex="-1"
            class="text-xl font-bold leading-tight text-text-heading md:text-2xl"
        >
            {{ t('patient.medications.overview.title') }}
        </h2>

        <div>
            <p :class="overviewSectionHeadingClass">
                {{ t('patient.medications.overview.sectionMedicine') }}
            </p>
            <dl class="rounded-2xl border border-border/70 bg-surface px-4 py-1 md:px-5">
                <div :class="summaryRowGroupClass">
                    <dt :class="summaryLabelClass">{{ t('patient.medications.fields.name') }}</dt>
                    <dd :class="summaryDdClass">
                        <span :class="summaryValueClass">{{ props.form.name.trim() || '—' }}</span>
                        <IconActionButton v-if="showStepNavigation"
                            :ariaLabel="summaryRowAria('fields.name')"
                            :disabled="props.form.processing"
                            @click="activateSummaryRow(1, 'name')"
                        >
                            <Pencil
                                class="size-5"
                                aria-hidden="true"
                            />
                        </IconActionButton>
                    </dd>
                </div>
                <div :class="summaryRowGroupClass">
                    <dt :class="summaryLabelClass">{{ t('patient.medications.fields.type') }}</dt>
                    <dd :class="summaryDdClass">
                        <span :class="summaryValueClass">{{ typeLabel }}</span>
                        <IconActionButton v-if="showStepNavigation"
                            :ariaLabel="summaryRowAria('fields.type')"
                            :disabled="props.form.processing"
                            @click="activateSummaryRow(1, summaryTypeFocusSuffix)"
                        >
                            <Pencil
                                class="size-5"
                                aria-hidden="true"
                            />
                        </IconActionButton>
                    </dd>
                </div>
                <div :class="summaryRowGroupClass">
                    <dt :class="summaryLabelClass">{{ t('patient.medications.fields.dose') }}</dt>
                    <dd :class="summaryDdClass">
                        <span :class="summaryValueClass">
                            {{ props.form.dose.trim() || '—' }}
                            <span class="text-text-muted">
                                {{ medicationDoseUnitChipForAmount(t, props.form.dose, props.form.dose_unit) }}
                            </span>
                        </span>
                        <IconActionButton v-if="showStepNavigation"
                            :ariaLabel="summaryRowAria('fields.dose')"
                            :disabled="props.form.processing"
                            @click="activateSummaryRow(1, 'dose')"
                        >
                            <Pencil
                                class="size-5"
                                aria-hidden="true"
                            />
                        </IconActionButton>
                    </dd>
                </div>
                <div :class="summaryRowGroupClass">
                    <dt :class="summaryLabelClass">{{ t('patient.medications.fields.strength') }}</dt>
                    <dd :class="summaryDdClass">
                        <span :class="summaryValueClass">
                            {{
                                medicationStrengthDisplayValue(props.form).trim() || '—'
                            }}
                        </span>
                        <IconActionButton
                            v-if="showStepNavigation"
                            :ariaLabel="summaryRowAria('fields.strength')"
                            :disabled="props.form.processing"
                            @click="activateSummaryRow(1, 'strength')"
                        >
                            <Pencil
                                class="size-5"
                                aria-hidden="true"
                            />
                        </IconActionButton>
                    </dd>
                </div>
                <div
                    v-if="props.form.note.trim().length > 0 || props.alwaysShowNoteSummaryRow"
                    :class="summaryRowGroupClass"
                >
                    <dt :class="summaryLabelClass">{{ t('patient.medications.fields.note') }}</dt>
                    <dd :class="summaryDdClass">
                        <span
                            :class="cn(summaryValueClass, 'whitespace-pre-wrap wrap-break-word')"
                        >
                            {{ props.form.note.trim().length > 0 ? props.form.note.trim() : '—' }}
                        </span>
                        <IconActionButton v-if="showStepNavigation"
                            :ariaLabel="summaryRowAria('fields.note')"
                            :disabled="props.form.processing"
                            @click="activateSummaryRow(6, 'note')"
                        >
                            <Pencil
                                class="size-5"
                                aria-hidden="true"
                            />
                        </IconActionButton>
                    </dd>
                </div>
            </dl>
        </div>

        <div>
            <p :class="overviewSectionHeadingClass">
                {{ t('patient.medications.overview.sectionStock') }}
            </p>
            <dl class="rounded-2xl border border-border/70 bg-surface px-4 py-1 md:px-5">
                <div :class="summaryRowGroupClass">
                    <dt :class="summaryLabelClass">{{ t('patient.medications.fields.currentStock') }}</dt>
                    <dd :class="summaryDdClass">
                        <span :class="cn(summaryValueClass, 'tabular-nums')">
                            {{ currentStockSummary.length > 0 ? currentStockSummary : '—' }}
                        </span>
                        <IconActionButton
                            v-if="showStepNavigation"
                            :ariaLabel="summaryRowAria('fields.currentStock')"
                            :disabled="props.form.processing"
                            @click="activateSummaryRow(6, 'current-stock')"
                        >
                            <Pencil
                                class="size-5"
                                aria-hidden="true"
                            />
                        </IconActionButton>
                    </dd>
                </div>
            </dl>
        </div>

        <div>
            <p :class="overviewSectionHeadingClass">
                {{ t('patient.medications.overview.sectionSchedule') }}
            </p>
            <dl class="rounded-2xl border border-border/70 bg-surface px-4 py-1 md:px-5">
                <div :class="summaryRowGroupClass">
                    <dt :class="summaryLabelClass">{{ t('patient.medications.fields.mealTiming') }}</dt>
                    <dd :class="summaryDdClass">
                        <span :class="summaryValueClass">{{ mealTimingLabel }}</span>
                        <IconActionButton
                            v-if="showStepNavigation"
                            :ariaLabel="summaryRowAria('fields.mealTiming')"
                            :disabled="props.form.processing"
                            @click="activateSummaryRow(2, summaryMealTimingFocusSuffix)"
                        >
                            <Pencil
                                class="size-5"
                                aria-hidden="true"
                            />
                        </IconActionButton>
                    </dd>
                </div>
                <div :class="summaryRowGroupClass">
                    <dt :class="summaryLabelClass">
                        {{ t('patient.medications.fields.intakeFrequency') }}
                    </dt>
                    <dd :class="summaryDdClass">
                        <span :class="summaryValueClass">{{ intakeFrequencySummary }}</span>
                        <IconActionButton
                            v-if="showStepNavigation"
                            :ariaLabel="summaryRowAria('fields.intakeFrequency')"
                            :disabled="props.form.processing"
                            @click="activateSummaryRow(2, summaryIntakeFrequencyFocusSuffix)"
                        >
                            <Pencil
                                class="size-5"
                                aria-hidden="true"
                            />
                        </IconActionButton>
                    </dd>
                </div>
                <div :class="summaryRowGroupClass">
                    <dt :class="summaryLabelClass">{{ t('patient.medications.fields.timesPerDay') }}</dt>
                    <dd :class="summaryDdClass">
                        <span :class="summaryValueClass">
                            {{ props.form.schedule.times_per_day.trim() || '—' }}
                        </span>
                        <IconActionButton
                            v-if="showStepNavigation"
                            :ariaLabel="summaryRowAria('fields.timesPerDay')"
                            :disabled="props.form.processing"
                            @click="activateSummaryRow(3, summaryTimesPerDayFocusSuffix)"
                        >
                            <Pencil
                                class="size-5"
                                aria-hidden="true"
                            />
                        </IconActionButton>
                    </dd>
                </div>
                <div :class="summaryRowGroupClass">
                    <dt :class="summaryLabelClass">{{ t('patient.medications.fields.doseTime') }}</dt>
                    <dd :class="summaryDdClass">
                        <span :class="summaryValueClass">{{ doseTimesJoined }}</span>
                        <IconActionButton
                            v-if="showStepNavigation"
                            :ariaLabel="summaryRowAria('fields.doseTime')"
                            :disabled="props.form.processing"
                            @click="activateSummaryRow(4, 'schedule-dose-time-0')"
                        >
                            <Pencil
                                class="size-5"
                                aria-hidden="true"
                            />
                        </IconActionButton>
                    </dd>
                </div>
                <div :class="summaryRowGroupClass">
                    <dt :class="summaryLabelClass">{{ t('patient.medications.fields.startDate') }}</dt>
                    <dd :class="summaryDdClass">
                        <span :class="summaryValueClass">{{ props.form.schedule.start_date.trim() || '—' }}</span>
                        <IconActionButton
                            v-if="showStepNavigation"
                            :ariaLabel="summaryRowAria('fields.startDate')"
                            :disabled="props.form.processing"
                            @click="activateSummaryRow(5, 'schedule-start-date')"
                        >
                            <Pencil
                                class="size-5"
                                aria-hidden="true"
                            />
                        </IconActionButton>
                    </dd>
                </div>
                <div :class="summaryRowGroupClass">
                    <dt :class="summaryLabelClass">{{ t('patient.medications.fields.endDate') }}</dt>
                    <dd :class="summaryDdClass">
                        <span :class="summaryValueClass">{{
                            props.form.schedule.end_date.trim().length > 0
                                ? props.form.schedule.end_date.trim()
                                : t('patient.medications.intakePeriodPresets.ongoing')
                        }}</span>
                        <IconActionButton
                            v-if="showStepNavigation"
                            :ariaLabel="summaryRowAria('fields.endDate')"
                            :disabled="props.form.processing"
                            @click="activateSummaryRow(5, 'schedule-end-date')"
                        >
                            <Pencil
                                class="size-5"
                                aria-hidden="true"
                            />
                        </IconActionButton>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</template>
