import { z } from 'zod';

import {
    MEDICATION_INTAKE_FREQUENCY_VALUES,
    MEDICATION_MEAL_TIMING_VALUES,
} from '@/lib/types';
import {
    inclusiveCalendarDaysBetweenIsoDates,
    medicationScheduleEndDateIsoInclusiveLocal,
} from '../schedule/medicationScheduleDuration';
import { isMemberOf, parseMedicationTimesPerDayCount } from '../validation/medicationFormValidationPrimitives';
import { medicationWizardStepValidation } from './wizardStepMessages';

function applyMedicationWizardScheduleTimingRefinement(
    data: {
        meal_timing: string;
        intake_frequency: string;
        intake_weekdays: readonly number[];
    },
    ctx: z.core.$RefinementCtx,
): void {
    if (data.meal_timing === '' || !isMemberOf(MEDICATION_MEAL_TIMING_VALUES, data.meal_timing)) {
        ctx.addIssue({
            code: 'custom',
            message: medicationWizardStepValidation('scheduleMealTimingRequired'),
            path: ['meal_timing'],
        });
    }

    if (
        data.intake_frequency === '' ||
        !isMemberOf(MEDICATION_INTAKE_FREQUENCY_VALUES, data.intake_frequency)
    ) {
        ctx.addIssue({
            code: 'custom',
            message: medicationWizardStepValidation('scheduleIntakeFrequencyRequired'),
            path: ['intake_frequency'],
        });

        return;
    }

    if (data.intake_frequency !== 'weekdays') {
        return;
    }

    if (data.intake_weekdays.length < 1) {
        ctx.addIssue({
            code: 'custom',
            message: medicationWizardStepValidation('scheduleIntakeWeekdaysRequired'),
            path: ['intake_weekdays'],
        });

        return;
    }

    if (data.intake_weekdays.some((d) => !Number.isInteger(d) || d < 1 || d > 7)) {
        ctx.addIssue({
            code: 'custom',
            message: medicationWizardStepValidation('scheduleIntakeWeekdaysInvalid'),
            path: ['intake_weekdays'],
        });
    }
}

function applyMedicationWizardTimesPerDayRefinement(
    data: { times_per_day: string },
    ctx: z.core.$RefinementCtx,
): void {
    const timesTrimmed = data.times_per_day.trim();

    if (timesTrimmed.length < 1) {
        ctx.addIssue({
            code: 'custom',
            message: medicationWizardStepValidation('scheduleTimesPerDayRequired'),
            path: ['times_per_day'],
        });

        return;
    }

    if (parseMedicationTimesPerDayCount(timesTrimmed) === null) {
        ctx.addIssue({
            code: 'custom',
            message: medicationWizardStepValidation('scheduleTimesPerDayInvalid'),
            path: ['times_per_day'],
        });
    }
}

function applyMedicationWizardSnoozeTimesRefinement(
    data: { times_per_day: string; snooze_time_slots: readonly string[] },
    ctx: z.core.$RefinementCtx,
): void {
    const timesCount = parseMedicationTimesPerDayCount(data.times_per_day.trim());

    if (timesCount === null) {
        return;
    }

    const snoozeSlots = Array.from({ length: timesCount }, (_, i) => {
        const raw = data.snooze_time_slots[i] ?? '';

        return raw.trim();
    });

    if (snoozeSlots.some((slot) => slot.length < 1)) {
        ctx.addIssue({
            code: 'custom',
            message: medicationWizardStepValidation('scheduleSnoozeTimeRequired'),
            path: ['snooze_time'],
        });

        return;
    }

    const invalid = snoozeSlots.some((slot) => {
        if (!/^\d+$/.test(slot)) {
            return true;
        }

        const minutes = Number(slot);

        return !Number.isInteger(minutes) || minutes < 0 || minutes > 24 * 60;
    });

    if (invalid) {
        ctx.addIssue({
            code: 'custom',
            message: medicationWizardStepValidation('scheduleSnoozeTimeInvalid'),
            path: ['snooze_time'],
        });
    }
}

function applyMedicationWizardDoseTimesRefinement(
    data: { times_per_day: string; dose_time_slots: readonly string[] },
    ctx: z.core.$RefinementCtx,
): void {
    const timesCount = parseMedicationTimesPerDayCount(data.times_per_day.trim());

    if (timesCount === null) {
        return;
    }

    const slotsForDay = Array.from({ length: timesCount }, (_, i) => {
        const raw = data.dose_time_slots[i] ?? '';

        return raw.trim();
    });

    if (slotsForDay.some((slot) => slot.length < 1)) {
        ctx.addIssue({
            code: 'custom',
            message: medicationWizardStepValidation('scheduleDoseTimeRequired'),
            path: ['dose_time'],
        });

        return;
    }

    if (slotsForDay.join(', ').length > 500) {
        ctx.addIssue({
            code: 'custom',
            message: medicationWizardStepValidation('scheduleDoseTimeMax'),
            path: ['dose_time'],
        });
    }
}

function applyMedicationWizardDurationRefinement(
    data: { start_date: string; end_date: string },
    ctx: z.core.$RefinementCtx,
): void {
    const startTrimmed = data.start_date.trim();
    const endTrimmed = data.end_date.trim();

    if (startTrimmed.length < 1) {
        ctx.addIssue({
            code: 'custom',
            message: medicationWizardStepValidation(
                endTrimmed.length < 1
                    ? 'scheduleIntakePeriodRequired'
                    : 'scheduleStartDateRequired',
            ),
            path: ['start_date'],
        });
    } else if (medicationScheduleEndDateIsoInclusiveLocal(startTrimmed, 1) === null) {
        ctx.addIssue({
            code: 'custom',
            message: medicationWizardStepValidation('scheduleStartDateInvalid'),
            path: ['start_date'],
        });
    }

    if (endTrimmed.length < 1) {
        return;
    }

    if (medicationScheduleEndDateIsoInclusiveLocal(endTrimmed, 1) === null) {
        ctx.addIssue({
            code: 'custom',
            message: medicationWizardStepValidation('scheduleEndDateInvalid'),
            path: ['end_date'],
        });
    }

    const hasStartIssue =
        startTrimmed.length < 1 || medicationScheduleEndDateIsoInclusiveLocal(startTrimmed, 1) === null;
    const hasEndIssue =
        endTrimmed.length < 1 || medicationScheduleEndDateIsoInclusiveLocal(endTrimmed, 1) === null;

    if (hasStartIssue || hasEndIssue) {
        return;
    }

    const spanDays = inclusiveCalendarDaysBetweenIsoDates(startTrimmed, endTrimmed);

    if (spanDays === null) {
        ctx.addIssue({
            code: 'custom',
            message: medicationWizardStepValidation('scheduleEndDateBeforeStart'),
            path: ['end_date'],
        });

        return;
    }

    if (spanDays > 366) {
        ctx.addIssue({
            code: 'custom',
            message: medicationWizardStepValidation('scheduleCourseMaxDays'),
            path: ['end_date'],
        });
    }
}

export const medicationWizardScheduleTimingFieldsSchema = z
    .object({
        meal_timing: z.string(),
        intake_frequency: z.string(),
        intake_weekdays: z.array(z.number()),
    })
    .superRefine(applyMedicationWizardScheduleTimingRefinement);

export const medicationWizardTimesPerDayOnlySchema = z
    .object({ times_per_day: z.string() })
    .superRefine(applyMedicationWizardTimesPerDayRefinement);

export const medicationWizardDoseTimesFieldsSchema = z
    .object({
        times_per_day: z.string(),
        dose_time_slots: z.array(z.string()),
        snooze_time_slots: z.array(z.string()),
    })
    .superRefine(applyMedicationWizardDoseTimesRefinement)
    .superRefine(applyMedicationWizardSnoozeTimesRefinement);

export const medicationWizardDurationFieldsSchema = z
    .object({
        start_date: z.string(),
        end_date: z.string(),
    })
    .superRefine(applyMedicationWizardDurationRefinement);

export const medicationWizardScheduleSliceSchema = z
    .object({
        meal_timing: z.string(),
        intake_frequency: z.string(),
        intake_weekdays: z.array(z.number()),
        times_per_day: z.string(),
        dose_time_slots: z.array(z.string()),
        snooze_time_slots: z.array(z.string()),
        start_date: z.string(),
        end_date: z.string(),
    })
    .superRefine(applyMedicationWizardScheduleTimingRefinement)
    .superRefine(applyMedicationWizardTimesPerDayRefinement)
    .superRefine(applyMedicationWizardDoseTimesRefinement)
    .superRefine(applyMedicationWizardSnoozeTimesRefinement)
    .superRefine(applyMedicationWizardDurationRefinement);
