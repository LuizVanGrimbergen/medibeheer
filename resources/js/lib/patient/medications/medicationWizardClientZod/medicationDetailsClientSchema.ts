import { z } from 'zod';

import { MEDICATION_DOSE_UNIT_VALUES, MEDICATION_TYPE_VALUES } from '@/lib/types';
import { isMemberOf } from '../validation/medicationFormValidationPrimitives';
import { medicationWizardStepValidation } from './wizardStepMessages';
import { trimmedNonEmptyMax } from './wizardStringFieldPatterns';

export const medicationWizardDetailsSchema = z.object({
    name: trimmedNonEmptyMax(500, 'nameRequired', 'nameMax'),
    dose: trimmedNonEmptyMax(500, 'doseRequired', 'doseMax'),
    dose_unit: z.string().superRefine((val, ctx) => {
        if (val === '' || !isMemberOf(MEDICATION_DOSE_UNIT_VALUES, val)) {
            ctx.addIssue({
                code: 'custom',
                message: medicationWizardStepValidation('doseUnitRequired'),
            });
        }
    }),
    type_medication: z.string().superRefine((val, ctx) => {
        if (val === '' || !isMemberOf(MEDICATION_TYPE_VALUES, val)) {
            ctx.addIssue({
                code: 'custom',
                message: medicationWizardStepValidation('typeRequired'),
            });
        }
    }),
});
