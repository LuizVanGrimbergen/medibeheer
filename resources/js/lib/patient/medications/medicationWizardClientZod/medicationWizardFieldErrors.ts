import type { z } from 'zod';

import type { MedicationFormWizardStep } from '@/Components/Patient/Medications/form/MedicationFormTypes';

export function medicationWizardZodIssuesToFlatFieldErrors(
    issues: z.core.$ZodIssue[],
): Partial<Record<string, string>> {
    const out: Partial<Record<string, string>> = {};

    for (const issue of issues) {
        const path = issue.path.map(String).join('.');

        if (path.length < 1) {
            continue;
        }

        if (out[path] !== undefined) {
            continue;
        }

        out[path] = issue.message;
    }

    return out;
}

export function prefixScheduleFieldErrors(
    inner: Partial<Record<string, string>>,
): Partial<Record<string, string>> {
    const out: Partial<Record<string, string>> = {};

    for (const [key, message] of Object.entries(inner)) {
        if (message === undefined || message.length < 1) {
            continue;
        }

        out[`schedule.${key}`] = message;
    }

    return out;
}

export function mapMedicationWizardDurationStepFieldErrors(
    inner: Partial<Record<string, string>>,
): Partial<Record<string, string>> {
    const out: Partial<Record<string, string>> = {};

    for (const [key, message] of Object.entries(inner)) {
        if (message === undefined || message.length < 1) {
            continue;
        }

        if (key === 'start_date' || key === 'end_date') {
            out[`schedule.${key}`] = message;

            continue;
        }

        out[key] = message;
    }

    return out;
}

export function hasNonEmptyFieldMessage(
    fieldErrors: Partial<Record<string, string>>,
): boolean {
    return Object.values(fieldErrors).some(
        (message) => message !== undefined && message.length > 0,
    );
}

export function medicationWizardStepAfterFullClientParseFailure(
    flat: Partial<Record<string, string>>,
): MedicationFormWizardStep {
    const hasKey = (key: string): boolean => {
        const m = flat[key];

        return m !== undefined && m.length > 0;
    };

    const hasDetailErrors =
        hasKey('name') ||
        hasKey('dose') ||
        hasKey('dose_unit') ||
        hasKey('type_medication') ||
        hasKey('strength') ||
        hasKey('strength_amount') ||
        hasKey('strength_unit');

    const hasTimingErrors =
        hasKey('schedule.meal_timing') ||
        hasKey('schedule.intake_frequency') ||
        hasKey('schedule.intake_weekdays');

    const hasTimesPerDayErrors = hasKey('schedule.times_per_day');
    const hasDoseSlotErrors =
        hasKey('schedule.dose_time') || hasKey('schedule.snooze_time');
    const hasDurationErrors =
        hasKey('schedule.start_date') || hasKey('schedule.end_date');
    const hasStepSixErrors =
        hasKey('note') ||
        hasKey('current_stock') ||
        hasKey('stock_pieces_per_package');

    if (hasDetailErrors) {
        return 1;
    }

    if (hasTimingErrors) {
        return 2;
    }

    if (hasTimesPerDayErrors) {
        return 3;
    }

    if (hasDoseSlotErrors) {
        return 4;
    }

    if (hasDurationErrors) {
        return 5;
    }

    if (hasStepSixErrors) {
        return 6;
    }

    return 1;
}
