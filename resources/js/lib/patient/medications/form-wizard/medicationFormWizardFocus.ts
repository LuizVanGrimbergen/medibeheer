import { MEDICATION_MEAL_TIMING_VALUES } from '@/lib/types';

export function focusMedicationWizardNameField(idPrefix: string): void {
    document.getElementById(`${idPrefix}-name`)?.focus({ preventScroll: true });
}

export function focusMedicationWizardMealTimingField(idPrefix: string, mealTimingRaw: string): void {
    const mealTiming =
        mealTimingRaw === '' ? MEDICATION_MEAL_TIMING_VALUES[0] : mealTimingRaw;

    document
        .getElementById(`${idPrefix}-schedule-meal-timing-option-${mealTiming}`)
        ?.focus({ preventScroll: true });
}

export function focusMedicationWizardTimesPerDayField(idPrefix: string, timesPerDayTrimmed: string): void {
    if (/^[1-4]$/.test(timesPerDayTrimmed)) {
        document
            .getElementById(`${idPrefix}-schedule-times-per-day-option-${timesPerDayTrimmed}`)
            ?.focus({ preventScroll: true });

        return;
    }

    document
        .getElementById(`${idPrefix}-schedule-times-per-day-custom`)
        ?.focus({ preventScroll: true });
}

export function focusMedicationWizardFirstDoseTimeField(idPrefix: string): void {
    document.getElementById(`${idPrefix}-schedule-dose-time-0`)?.focus({ preventScroll: true });
}

export function focusMedicationWizardScheduleStartDateField(idPrefix: string): void {
    document
        .getElementById(`${idPrefix}-schedule-start-date`)
        ?.focus({ preventScroll: true });
}

export function focusMedicationWizardStockOrNoteField(idPrefix: string): void {
    const stockEl = document.getElementById(`${idPrefix}-current-stock`);

    if (stockEl !== null) {
        stockEl.focus({ preventScroll: true });

        return;
    }

    document.getElementById(`${idPrefix}-note`)?.focus({ preventScroll: true });
}

export function focusMedicationWizardSummaryTitleField(idPrefix: string): void {
    document.getElementById(`${idPrefix}-create-summary-title`)?.focus({ preventScroll: true });
}

export function tryFocusMedicationWizardElementBySuffix(idPrefix: string, suffix: string): boolean {
    const el = document.getElementById(`${idPrefix}-${suffix}`);

    if (el === null) {
        return false;
    }

    el.focus({ preventScroll: true });

    return true;
}
