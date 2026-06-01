import {
    MEDICATION_STRENGTH_UNIT_VALUES,
    medicationStrengthUnitStorageLabel,
    type MedicationStrengthUnitValue,
} from '@/lib/patient/medications/options/medicationStrengthUnitForm';

export function buildMedicationStrengthFromParts(
    doseUnit: string,
    strengthAmount: string,
    strengthUnit: string,
): string | null {
    const amountTrimmed = strengthAmount.trim();

    if (amountTrimmed.length < 1) {
        return null;
    }

    if (
        !(MEDICATION_STRENGTH_UNIT_VALUES as readonly string[]).includes(strengthUnit)
    ) {
        return null;
    }

    const unitLabel = medicationStrengthUnitStorageLabel(
        strengthUnit as MedicationStrengthUnitValue,
    );
    const suffix = doseUnit === 'drop' ? ' per druppel' : '';

    return `${amountTrimmed} ${unitLabel}${suffix}`;
}

export function resolveMedicationStrengthForPayload(data: {
    dose_unit: string;
    strength: string;
    strength_amount: string;
    strength_unit: string;
}): string | null {
    const fromParts = buildMedicationStrengthFromParts(
        data.dose_unit,
        data.strength_amount,
        data.strength_unit,
    );

    if (fromParts !== null) {
        return fromParts;
    }

    const trimmed = data.strength.trim();

    return trimmed === '' ? null : trimmed;
}
