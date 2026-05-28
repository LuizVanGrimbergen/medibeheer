import { z } from 'zod';

export type PatientEmailFieldMessages = {
    required: string;
    invalid: string;
};

export function validatePatientEmailField(
    raw: string,
    messages: PatientEmailFieldMessages,
): string | null {
    const normalized = raw.trim().toLowerCase();

    if (normalized.length < 1) {
        return messages.required;
    }

    const parsed = z.string().email().safeParse(normalized);

    if (!parsed.success) {
        return messages.invalid;
    }

    return null;
}
