import {
    isCustomIntakeFrequencyInterval,
    MEDICATION_INTAKE_FREQUENCY_SUMMARY_OPTION_PRESETS,
} from '@/lib/patient/medications/schedule/medicationIntakeFrequencyDisplay';
import type { MedicationTypeValue } from '@/lib/types';
import { MEDICATION_MEAL_TIMING_VALUES } from '@/lib/types';

export type MedicationWizardIntakeFrequencyFocusSchedule = {
    intake_frequency: string;
    intake_weekdays: readonly number[];
};

export function resolveMedicationWizardMealTimingFocusSuffix(
    mealTimingRaw: string,
): string {
    const mealTiming =
        mealTimingRaw === '' ? MEDICATION_MEAL_TIMING_VALUES[0] : mealTimingRaw;

    return `schedule-meal-timing-option-${mealTiming}`;
}

export function resolveMedicationWizardTimesPerDayFocusSuffix(
    timesPerDayTrimmed: string,
): string {
    if (timesPerDayTrimmed === '') {
        return 'schedule-times-per-day-count-option-1';
    }

    if (/^[1-4]$/.test(timesPerDayTrimmed)) {
        return `schedule-times-per-day-count-option-${timesPerDayTrimmed}`;
    }

    return 'schedule-times-per-day-count-custom-trigger';
}

export function resolveMedicationWizardIntakeFrequencyFocusSuffix(
    schedule: MedicationWizardIntakeFrequencyFocusSchedule,
): string {
    const frequency = schedule.intake_frequency;

    if (frequency === 'weekdays') {
        if (schedule.intake_weekdays.length < 1) {
            return 'schedule-intake-frequency-option-weekdays';
        }

        return `schedule-intake-weekday-${schedule.intake_weekdays[0]}`;
    }

    if (
        (
            MEDICATION_INTAKE_FREQUENCY_SUMMARY_OPTION_PRESETS as readonly string[]
        ).includes(frequency)
    ) {
        return `schedule-intake-frequency-option-${frequency}`;
    }

    if (isCustomIntakeFrequencyInterval(frequency)) {
        return 'schedule-intake-frequency-custom-days';
    }

    return 'schedule-intake-frequency-option-daily';
}

export function resolveMedicationWizardTypeFocusSuffix(
    typeMedication: MedicationTypeValue | '',
): string {
    if (typeMedication === '') {
        return 'type';
    }

    return `type-option-${typeMedication}`;
}
