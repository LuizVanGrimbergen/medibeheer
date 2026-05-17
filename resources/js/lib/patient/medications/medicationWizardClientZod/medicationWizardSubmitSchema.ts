import { z } from 'zod';

import { medicationWizardDetailsSchema } from './medicationDetailsClientSchema';
import { medicationWizardScheduleSliceSchema } from './medicationScheduleClientSchema';
import { medicationWizardStepValidation } from './wizardStepMessages';
import { trimmedNonEmptyMax } from './wizardStringFieldPatterns';

export const medicationWizardCreateFormClientSchemaFinal = z.intersection(
    medicationWizardDetailsSchema,
    z.object({
        current_stock: trimmedNonEmptyMax(500, 'stockCurrentRequired', 'stockCurrentMax'),
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
