import { parseMedicationStockNumericValue } from '@/lib/patient/medications/stock/parseMedicationStockNumericValue';

export function medicationStockProgressPercent(
    currentStock: string,
    lowStock: string,
    doseUnit?: string | null,
): number | null {
    const current = parseMedicationStockNumericValue(currentStock, doseUnit);
    const low = parseMedicationStockNumericValue(lowStock, doseUnit);

    if (current === null || low === null) {
        return null;
    }

    const scale = Math.max(low * 2, current, Number.EPSILON);

    return Math.min(100, Math.round((current / scale) * 100));
}
