import type { MedicationDoseUnitValue } from '@/lib/types';

export const MEDICATION_STOCK_UNIT_SUFFIX_DOSE_UNITS = [
    'milligram',
    'gram',
    'milliliter',
    'piece',
    'drop',
    'injection',
    'unit',
    'sachet',
] as const satisfies readonly MedicationDoseUnitValue[];

export type MedicationStockUnitSuffixDoseUnit =
    (typeof MEDICATION_STOCK_UNIT_SUFFIX_DOSE_UNITS)[number];

export function medicationStockSupportsUnitSuffix(
    doseUnit: string | null | undefined,
): doseUnit is MedicationStockUnitSuffixDoseUnit {
    if (doseUnit === null || doseUnit === undefined || doseUnit === '') {
        return false;
    }

    return (MEDICATION_STOCK_UNIT_SUFFIX_DOSE_UNITS as readonly string[]).includes(doseUnit);
}
