export function isMedicationStockLow(currentStock: string, lowStock: string): boolean {
    const current = Number.parseFloat(currentStock.trim());
    const low = Number.parseFloat(lowStock.trim());

    if (!Number.isFinite(current) || !Number.isFinite(low)) {
        return false;
    }

    return current <= low;
}
