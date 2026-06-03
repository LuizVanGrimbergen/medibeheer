import { z } from 'zod';

import {
    MEDICATION_DOSE_UNIT_FORM_VALUES,
    medicationDoseUnitRequiresStrength,
} from '@/lib/patient/medications/options/medicationDoseUnitForm';
import { MEDICATION_STRENGTH_UNIT_VALUES } from '@/lib/patient/medications/options/medicationStrengthUnitForm';
import { MEDICATION_TYPE_VALUES } from '@/lib/types';
import { isMemberOf } from '../validation/medicationFormValidationPrimitives';
import { medicationWizardStepValidation } from './wizardStepMessages';
import { trimmedNonEmptyMax } from './wizardStringFieldPatterns';

export const medicationWizardDetailsSchema = z
    .object({
        name: trimmedNonEmptyMax(500, 'nameRequired', 'nameMax'),
        dose: trimmedNonEmptyMax(500, 'doseRequired', 'doseMax'),
        dose_unit: z.string().superRefine((val, ctx) => {
            const allowed =
                val === 'drop' ||
                val === 'unit' ||
                isMemberOf(MEDICATION_DOSE_UNIT_FORM_VALUES, val);

            if (val === '' || !allowed) {
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
        strength: z.string(),
        strength_amount: z.string(),
        strength_unit: z.string(),
    })
    .superRefine((data, ctx) => {
        const amountTrimmed = data.strength_amount.trim();
        const strengthRequired = medicationDoseUnitRequiresStrength(
            data.dose_unit,
        );

        if (strengthRequired && amountTrimmed.length < 1) {
            ctx.addIssue({
                code: 'custom',
                message: medicationWizardStepValidation(
                    'strengthAmountRequired',
                ),
                path: ['strength_amount'],
            });
        }

        if (amountTrimmed.length < 1) {
            return;
        }

        if (
            data.strength_unit === '' ||
            !isMemberOf(MEDICATION_STRENGTH_UNIT_VALUES, data.strength_unit)
        ) {
            ctx.addIssue({
                code: 'custom',
                message: medicationWizardStepValidation('strengthUnitRequired'),
                path: ['strength_unit'],
            });
        }
    });
