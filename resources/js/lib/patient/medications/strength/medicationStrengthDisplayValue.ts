import { medicationDoseUnitRequiresStrength } from '@/lib/patient/medications/options/medicationDoseUnitForm';
import { buildMedicationStrengthFromParts } from '@/lib/patient/medications/strength/buildMedicationStrengthFromParts';

export function medicationStrengthDisplayValue(data: {
    dose_unit: string;
    strength: string;
    strength_amount: string;
    strength_unit: string;
}): string {
    if (medicationDoseUnitRequiresStrength(data.dose_unit)) {
        return (
            buildMedicationStrengthFromParts(
                data.dose_unit,
                data.strength_amount,
                data.strength_unit,
            ) ?? ''
        );
    }

    return data.strength.trim();
}
