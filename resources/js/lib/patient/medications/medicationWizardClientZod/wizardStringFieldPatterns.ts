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

export function trimmedPositiveIntegerMax(
    maxValue: number,
    requiredKey: string,
    invalidKey: string,
    maxKey: string,
) {
    return z.string().superRefine((val, ctx) => {
        const trimmed = val.trim();

        if (trimmed.length < 1) {
            ctx.addIssue({
                code: 'custom',
                message: medicationWizardStepValidation(requiredKey),
            });

            return;
        }

        if (!/^\d+$/.test(trimmed)) {
            ctx.addIssue({
                code: 'custom',
                message: medicationWizardStepValidation(invalidKey),
            });

            return;
        }

        const parsed = Number.parseInt(trimmed, 10);

        if (parsed < 1 || parsed > maxValue) {
            ctx.addIssue({
                code: 'custom',
                message: medicationWizardStepValidation(maxKey),
            });
        }
    });
}
