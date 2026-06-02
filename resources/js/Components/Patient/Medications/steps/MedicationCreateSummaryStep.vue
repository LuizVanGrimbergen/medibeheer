<script setup lang="ts">
import { Pencil } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import type {
    MedicationCreateFormWithErrors,
    MedicationFormWizardStep,
} from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { IconActionButton } from '@/Components/ui/icon-action-button';
import { medicationTypeLabel } from '@/lib/patient/medications/display/medicationIntakeSlotDisplay';
import {
    resolveMedicationWizardIntakeFrequencyFocusSuffix,
    resolveMedicationWizardMealTimingFocusSuffix,
    resolveMedicationWizardTimesPerDayFocusSuffix,
    resolveMedicationWizardTypeFocusSuffix,
} from '@/lib/patient/medications/form-wizard/medicationFormWizardFocusSuffixes';
import { medicationDoseUnitChipForAmount } from '@/lib/patient/medications/options/medicationDoseUnitChipForAmount';
import { formatMedicationSnoozeMinutesLabelFromRaw } from '@/lib/patient/medications/schedule/formatMedicationSnoozeLabel';
import { medicationIntakeFrequencySummaryLabel } from '@/lib/patient/medications/schedule/medicationIntakeFrequencyDisplay';
import { formatMedicationStockDisplayAmount } from '@/lib/patient/medications/stock/formatMedicationStockDisplayAmount';
import { medicationStockDisplayDoseUnit } from '@/lib/patient/medications/stock/medicationStockDisplayDoseUnit';
import { medicationStrengthDisplayValue } from '@/lib/patient/medications/strength/medicationStrengthDisplayValue';
import { parseMedicationTimesPerDayCount } from '@/lib/patient/medications/validation/medicationFormValidationPrimitives';
import { patientFormLabelClass } from '@/lib/patient/patientFormFieldClasses';
import type {
    MedicationIntakeFrequencyValue,
    MedicationMealTimingValue,
    MedicationTypeValue,
} from '@/lib/types';
import { cn } from '@/lib/utils';

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

const intakeFrequencySummary = computed(() =>
    medicationIntakeFrequencySummaryLabel(
        t,
        props.form.schedule.intake_frequency as MedicationIntakeFrequencyValue | '',
        props.form.schedule.intake_weekdays,
    ),
);

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

    return medicationTypeLabel(t, value);
});

const doseTimesJoined = computed(() => {
    const count = parseMedicationTimesPerDayCount(props.form.schedule.times_per_day);

    if (count === null) {
        return '—';
    }

    const parts: string[] = [];

    for (let index = 0; index < count; index += 1) {
        const time = props.form.schedule.dose_time_slots[index]?.trim() ?? '';

        if (time.length < 1) {
            continue;
        }

        const snooze = props.form.schedule.snooze_time_slots[index] ?? '';
        const snoozeLabel = formatMedicationSnoozeMinutesLabelFromRaw(t, snooze);

        parts.push(`${time} (${snoozeLabel})`);
    }

    if (parts.length < 1) {
        return '—';
    }

    return parts.join(', ');
});

const doseUnitForStock = computed(() =>
    medicationStockDisplayDoseUnit(props.form.dose_unit, props.form.strength_unit),
);

const currentStockSummary = computed(() =>
    formatMedicationStockDisplayAmount(t, props.form.current_stock, doseUnitForStock.value),
);

const summaryMealTimingFocusSuffix = computed(() =>
    resolveMedicationWizardMealTimingFocusSuffix(props.form.schedule.meal_timing),
);

const summaryIntakeFrequencyFocusSuffix = computed(() =>
    resolveMedicationWizardIntakeFrequencyFocusSuffix(props.form.schedule),
);

const summaryTimesPerDayFocusSuffix = computed(() =>
    resolveMedicationWizardTimesPerDayFocusSuffix(props.form.schedule.times_per_day.trim()),
);

const summaryTypeFocusSuffix = computed(() =>
    resolveMedicationWizardTypeFocusSuffix(props.form.type_medication),
);

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
                            @click="activateSummaryRow(1, 'strength_amount')"
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
                            @click="activateSummaryRow(6, 'stock-boxes')"
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
