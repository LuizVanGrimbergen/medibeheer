import { z } from 'zod';

import { medicationWizardStepValidation } from './wizardStepMessages';

export function trimmedNonEmptyMax(maxLen: number, requiredKey: string, maxKey: string) {
    return z.string().superRefine((val, ctx) => {
        const trimmed = val.trim();

        if (trimmed.length < 1) {
            ctx.addIssue({
                code: 'custom',
                message: medicationWizardStepValidation(requiredKey),
            });

            return;
        }

        if (trimmed.length > maxLen) {
            ctx.addIssue({
                code: 'custom',
                message: medicationWizardStepValidation(maxKey),
            });
        }
    });
}
