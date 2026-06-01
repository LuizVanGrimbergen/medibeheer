import { buildMedicationStrengthFromParts } from '@/lib/patient/medications/strength/buildMedicationStrengthFromParts';

export function medicationStrengthDisplayValue(data: {
    dose_unit: string;
    strength: string;
    strength_amount: string;
    strength_unit: string;
}): string {
    const fromParts = buildMedicationStrengthFromParts(
        data.dose_unit,
        data.strength_amount,
        data.strength_unit,
    );

    if (fromParts !== null) {
        return fromParts;
    }

    return data.strength.trim();
}
