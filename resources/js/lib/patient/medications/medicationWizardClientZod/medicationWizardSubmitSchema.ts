import { z } from 'zod';

import { medicationWizardDetailsSchema } from './medicationDetailsClientSchema';
import { medicationWizardScheduleSliceSchema } from './medicationScheduleClientSchema';
import { medicationWizardStepValidation } from './wizardStepMessages';
import { trimmedNonEmptyMax } from './wizardStringFieldPatterns';

export const medicationWizardCreateFormClientSchemaFinal = z.object({
    name: medicationWizardDetailsSchema.shape.name,
    dose: medicationWizardDetailsSchema.shape.dose,
    dose_unit: medicationWizardDetailsSchema.shape.dose_unit,
    type_medication: medicationWizardDetailsSchema.shape.type_medication,
    color: medicationWizardDetailsSchema.shape.color,
    current_stock: trimmedNonEmptyMax(500, 'stockCurrentRequired', 'stockCurrentMax'),
    low_stock: trimmedNonEmptyMax(64, 'stockLowRequired', 'stockLowMax'),
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
});
