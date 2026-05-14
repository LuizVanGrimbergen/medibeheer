export function medicationStockProgressPercent(
    currentStock: string,
    lowStock: string,
): number | null {
    const current = Number.parseFloat(currentStock.trim());
    const low = Number.parseFloat(lowStock.trim());

    if (!Number.isFinite(current) || !Number.isFinite(low) || current < 0 || low < 0) {
        return null;
    }

    const scale = Math.max(low * 2, current, Number.EPSILON);

    return Math.min(100, Math.round((current / scale) * 100));
}
