import { parseMedicationStockNumericValue } from '@/lib/patient/medications/stock/parseMedicationStockNumericValue';

export type MedicationStockProgressTone = 'critical' | 'warning' | 'safe';

export function medicationStockProgressTone(
    currentStock: string,
    lowStock: string,
    doseUnit?: string | null,
): MedicationStockProgressTone | null {
    const current = parseMedicationStockNumericValue(currentStock, doseUnit);
    const low = parseMedicationStockNumericValue(lowStock, doseUnit);

    if (current === null || low === null) {
        return null;
    }

    if (low === 0) {
        if (current <= 0) {
            return 'critical';
        }

        return 'safe';
    }

    if (current <= low) {
        return 'critical';
    }

    if (current <= low * 1.5) {
        return 'warning';
    }

    return 'safe';
}
