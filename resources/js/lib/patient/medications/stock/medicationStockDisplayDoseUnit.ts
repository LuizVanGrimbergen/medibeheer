import { medicationDoseUnitRequiresStrength } from '@/lib/patient/medications/options/medicationDoseUnitForm';
import { MEDICATION_STRENGTH_UNIT_VALUES } from '@/lib/patient/medications/options/medicationStrengthUnitForm';

export function medicationStockDisplayDoseUnit(
    doseUnit: string | null | undefined,
    strengthUnit: string | null | undefined,
): string | null {
    if (doseUnit === null || doseUnit === undefined || doseUnit === '') {
        return null;
    }

    const strengthTrimmed = strengthUnit?.trim() ?? '';

    if (
        medicationDoseUnitRequiresStrength(doseUnit) &&
        strengthTrimmed.length > 0 &&
        (MEDICATION_STRENGTH_UNIT_VALUES as readonly string[]).includes(strengthTrimmed)
    ) {
        return strengthTrimmed;
    }

    return doseUnit;
}
