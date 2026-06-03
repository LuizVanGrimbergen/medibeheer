import {
    resolveMedicationWizardMealTimingFocusSuffix,
    resolveMedicationWizardTimesPerDayFocusSuffix,
} from '@/lib/patient/medications/form-wizard/medicationFormWizardFocusSuffixes';

export function focusMedicationWizardNameField(idPrefix: string): void {
    document.getElementById(`${idPrefix}-name`)?.focus({ preventScroll: true });
}

export function focusMedicationWizardMealTimingField(
    idPrefix: string,
    mealTimingRaw: string,
): void {
    document
        .getElementById(
            `${idPrefix}-${resolveMedicationWizardMealTimingFocusSuffix(mealTimingRaw)}`,
        )
        ?.focus({ preventScroll: true });
}

export function focusMedicationWizardTimesPerDayField(
    idPrefix: string,
    timesPerDayTrimmed: string,
): void {
    document
        .getElementById(
            `${idPrefix}-${resolveMedicationWizardTimesPerDayFocusSuffix(timesPerDayTrimmed)}`,
        )
        ?.focus({ preventScroll: true });
}

export function focusMedicationWizardFirstDoseTimeField(
    idPrefix: string,
): void {
    document
        .getElementById(`${idPrefix}-schedule-dose-time-0`)
        ?.focus({ preventScroll: true });
}

export function focusMedicationWizardScheduleStartDateField(
    idPrefix: string,
): void {
    document
        .getElementById(`${idPrefix}-schedule-start-date`)
        ?.focus({ preventScroll: true });
}

export function focusMedicationWizardStockOrNoteField(idPrefix: string): void {
    const stockBoxesEl = document.getElementById(`${idPrefix}-stock-boxes`);

    if (stockBoxesEl !== null) {
        stockBoxesEl.focus({ preventScroll: true });

        return;
    }

    const stockAmountEl = document.getElementById(`${idPrefix}-current-stock`);

    if (stockAmountEl !== null) {
        stockAmountEl.focus({ preventScroll: true });

        return;
    }

    document.getElementById(`${idPrefix}-note`)?.focus({ preventScroll: true });
}

export function focusMedicationWizardSummaryTitleField(idPrefix: string): void {
    document
        .getElementById(`${idPrefix}-create-summary-title`)
        ?.focus({ preventScroll: true });
}

export function tryFocusMedicationWizardElementBySuffix(
    idPrefix: string,
    suffix: string,
): boolean {
    const el = document.getElementById(`${idPrefix}-${suffix}`);

    if (el === null) {
        return false;
    }

    el.focus({ preventScroll: true });

    return true;
}
