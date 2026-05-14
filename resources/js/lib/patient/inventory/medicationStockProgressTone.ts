export type MedicationStockProgressTone = 'critical' | 'warning' | 'safe';

export function medicationStockProgressTone(
    currentStock: string,
    lowStock: string,
): MedicationStockProgressTone | null {
    const current = Number.parseFloat(currentStock.trim());
    const low = Number.parseFloat(lowStock.trim());

    if (!Number.isFinite(current) || !Number.isFinite(low) || current < 0 || low < 0) {
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
