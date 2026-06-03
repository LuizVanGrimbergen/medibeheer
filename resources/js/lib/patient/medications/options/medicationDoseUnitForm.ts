import type { MedicationDoseUnitValue } from '@/lib/types';
import { MEDICATION_DOSE_UNIT_VALUES } from '@/lib/types';

export const MEDICATION_DOSE_UNIT_FORM_VALUES =
    MEDICATION_DOSE_UNIT_VALUES.filter(
        (value): value is MedicationDoseUnitValue =>
            value !== 'unit' && value !== 'drop',
    );

export const MEDICATION_DOSE_UNITS_REQUIRING_STRENGTH = [
    'drop',
] as const satisfies readonly MedicationDoseUnitValue[];

export type MedicationDoseUnitRequiringStrength =
    (typeof MEDICATION_DOSE_UNITS_REQUIRING_STRENGTH)[number];

export function medicationDoseUnitRequiresStrength(
    doseUnit: string,
): doseUnit is MedicationDoseUnitRequiringStrength {
    return (
        MEDICATION_DOSE_UNITS_REQUIRING_STRENGTH as readonly string[]
    ).includes(doseUnit);
}
