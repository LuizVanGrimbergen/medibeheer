import { z } from 'zod';

import type {
    MedicationCreateFormState,
    MedicationCreateFormWithErrors,
} from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { medicationWizardDetailsSchema } from './medicationDetailsClientSchema';
import {
    medicationWizardDoseTimesFieldsSchema,
    medicationWizardDurationFieldsSchema,
    medicationWizardScheduleTimingFieldsSchema,
    medicationWizardTimesPerDayOnlySchema,
} from './medicationScheduleClientSchema';
import {
    hasNonEmptyFieldMessage,
    mapMedicationWizardDurationStepFieldErrors,
    medicationWizardStepAfterFullClientParseFailure,
    medicationWizardZodIssuesToFlatFieldErrors,
    prefixScheduleFieldErrors,
} from './medicationWizardFieldErrors';
import { medicationWizardCreateFormClientSchemaFinal } from './medicationWizardSubmitSchema';
import type {
    MedicationWizardClientParseResult,
    MedicationWizardSubmitClientValidationResult,
} from './types';
import { medicationWizardStepValidation } from './wizardStepMessages';
import {
    trimmedNonEmptyMax,
    trimmedPositiveIntegerMax,
} from './wizardStringFieldPatterns';

export function tryMedicationWizardDetailsStep(
    data: Pick<
        MedicationCreateFormState,
        | 'name'
        | 'dose'
        | 'dose_unit'
        | 'type_medication'
        | 'strength'
        | 'strength_amount'
        | 'strength_unit'
    >,
): MedicationWizardClientParseResult {
    const parsed = medicationWizardDetailsSchema.safeParse(data);

    if (parsed.success) {
        return { ok: true };
    }

    return {
        ok: false,
        fieldErrors: medicationWizardZodIssuesToFlatFieldErrors(
            parsed.error.issues,
        ),
    };
}

export function tryMedicationWizardScheduleTimingStep(
    schedule: MedicationCreateFormState['schedule'],
): MedicationWizardClientParseResult {
    const parsed = medicationWizardScheduleTimingFieldsSchema.safeParse({
        meal_timing: schedule.meal_timing,
        intake_frequency: schedule.intake_frequency,
        intake_weekdays: schedule.intake_weekdays,
    });

    if (parsed.success) {
        return { ok: true };
    }

    return {
        ok: false,
        fieldErrors: prefixScheduleFieldErrors(
            medicationWizardZodIssuesToFlatFieldErrors(parsed.error.issues),
        ),
    };
}

export function tryMedicationWizardTimesPerDayStep(
    schedule: MedicationCreateFormState['schedule'],
): MedicationWizardClientParseResult {
    const parsed = medicationWizardTimesPerDayOnlySchema.safeParse({
        times_per_day: schedule.times_per_day,
    });

    if (parsed.success) {
        return { ok: true };
    }

    return {
        ok: false,
        fieldErrors: prefixScheduleFieldErrors(
            medicationWizardZodIssuesToFlatFieldErrors(parsed.error.issues),
        ),
    };
}

export function tryMedicationWizardDoseTimesStep(
    schedule: MedicationCreateFormState['schedule'],
): MedicationWizardClientParseResult {
    const parsed = medicationWizardDoseTimesFieldsSchema.safeParse({
        times_per_day: schedule.times_per_day,
        dose_time_slots: [...schedule.dose_time_slots],
        snooze_time_slots: [...schedule.snooze_time_slots],
    });

    if (parsed.success) {
        return { ok: true };
    }

    return {
        ok: false,
        fieldErrors: prefixScheduleFieldErrors(
            medicationWizardZodIssuesToFlatFieldErrors(parsed.error.issues),
        ),
    };
}

export function tryMedicationWizardDurationStep(
    form: Pick<MedicationCreateFormState, 'schedule'>,
): MedicationWizardClientParseResult {
    const parsed = medicationWizardDurationFieldsSchema.safeParse({
        start_date: form.schedule.start_date,
        end_date: form.schedule.end_date,
    });

    if (parsed.success) {
        return { ok: true };
    }

    return {
        ok: false,
        fieldErrors: mapMedicationWizardDurationStepFieldErrors(
            medicationWizardZodIssuesToFlatFieldErrors(parsed.error.issues),
        ),
    };
}

export function tryMedicationWizardNoteStockStep(
    data: Pick<
        MedicationCreateFormState,
        'note' | 'current_stock' | 'stock_pieces_per_package'
    >,
): MedicationWizardClientParseResult {
    const parsed = z
        .object({
            note: z.string().superRefine((note, ctx) => {
                if (note.length > 2000) {
                    ctx.addIssue({
                        code: 'custom',
                        message: medicationWizardStepValidation('noteMax'),
                        path: ['note'],
                    });
                }
            }),
            current_stock: trimmedNonEmptyMax(
                500,
                'stockCurrentRequired',
                'stockCurrentMax',
            ),
            stock_pieces_per_package: trimmedPositiveIntegerMax(
                9999,
                'stockPiecesPerPackageRequired',
                'stockPiecesPerPackageInvalid',
                'stockPiecesPerPackageMax',
            ),
        })
        .safeParse(data);

    if (parsed.success) {
        return { ok: true };
    }

    return {
        ok: false,
        fieldErrors: medicationWizardZodIssuesToFlatFieldErrors(
            parsed.error.issues,
        ),
    };
}

export function evaluateMedicationWizardSubmitClientValidation(
    form: MedicationCreateFormWithErrors,
): MedicationWizardSubmitClientValidationResult {
    const parsed = medicationWizardCreateFormClientSchemaFinal.safeParse(
        form as unknown as MedicationCreateFormState,
    );

    if (parsed.success) {
        return { ok: true };
    }

    const mergedFieldErrors = medicationWizardZodIssuesToFlatFieldErrors(
        parsed.error.issues,
    );

    if (!hasNonEmptyFieldMessage(mergedFieldErrors)) {
        return { ok: true };
    }

    return {
        ok: false,
        mergedFieldErrors,
        step: medicationWizardStepAfterFullClientParseFailure(
            mergedFieldErrors,
        ),
    };
}
