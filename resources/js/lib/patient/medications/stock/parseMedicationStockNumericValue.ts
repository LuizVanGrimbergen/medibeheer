import {
    medicationStockSupportsUnitSuffix
    
} from '@/lib/patient/medications/stock/medicationStockSupportsUnitSuffix';
import type {MedicationStockUnitSuffixDoseUnit} from '@/lib/patient/medications/stock/medicationStockSupportsUnitSuffix';
import { medicationStockUnitSuffixPattern } from '@/lib/patient/medications/stock/medicationStockUnitSuffixPattern';

function stripKnownUnitSuffix(
    value: string,
    doseUnit: MedicationStockUnitSuffixDoseUnit,
): string {
    return value.replace(medicationStockUnitSuffixPattern(doseUnit), '').trim();
}

export function parseMedicationStockNumericValue(
    value: string,
    doseUnit?: string | null,
): number | null {
    let trimmed = value.trim();

    if (trimmed.length < 1) {
        return null;
    }

    if (medicationStockSupportsUnitSuffix(doseUnit)) {
        trimmed = stripKnownUnitSuffix(trimmed, doseUnit);
    }

    if (trimmed.length < 1) {
        return null;
    }

    const normalized = trimmed.replace(',', '.');

    if (!/^-?\d+(\.\d+)?$/.test(normalized)) {
        return null;
    }

    const parsed = Number.parseFloat(normalized);

    if (!Number.isFinite(parsed) || parsed < 0) {
        return null;
    }

    return parsed;
}
