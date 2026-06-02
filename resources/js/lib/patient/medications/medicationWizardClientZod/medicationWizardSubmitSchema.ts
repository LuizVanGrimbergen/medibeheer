import { z } from 'zod';

import { medicationWizardDetailsSchema } from './medicationDetailsClientSchema';
import {
    applyMedicationWizardPrescriptionExpiryRefinement,
    medicationWizardScheduleSliceSchema,
} from './medicationScheduleClientSchema';
import { medicationWizardStepValidation } from './wizardStepMessages';
import { trimmedNonEmptyMax, trimmedPositiveIntegerMax } from './wizardStringFieldPatterns';

export const medicationWizardCreateFormClientSchemaFinal = z.intersection(
    medicationWizardDetailsSchema,
    z.object({
        prescription_expiry_date: z.string().superRefine((value, ctx) => {
            applyMedicationWizardPrescriptionExpiryRefinement(
                { prescription_expiry_date: value },
                ctx,
            );
        }),
        current_stock: trimmedNonEmptyMax(500, 'stockCurrentRequired', 'stockCurrentMax'),
        stock_pieces_per_package: trimmedPositiveIntegerMax(
            9999,
            'stockPiecesPerPackageRequired',
            'stockPiecesPerPackageInvalid',
            'stockPiecesPerPackageMax',
        ),
        note: z.string().superRefine((note, ctx) => {
            if (note.length > 2000) {
                ctx.addIssue({
                    code: 'custom',
                    message: medicationWizardStepValidation('noteMax'),
                    path: ['note'],
                });
            }
        }),
        schedule: medicationWizardScheduleSliceSchema,
    }),
);
