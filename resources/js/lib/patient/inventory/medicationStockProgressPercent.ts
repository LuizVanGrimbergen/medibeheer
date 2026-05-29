import type { MedicationSupplyEstimateQuality } from '@/lib/types';

import { MEDICATION_SUPPLY_PROGRESS_FULL_DAYS } from './medicationSupplyDayThresholds';

export function medicationStockProgressPercent(
    supplyEstimateDays: number | null,
    supplyEstimateQuality: MedicationSupplyEstimateQuality,
): number | null {
    if (supplyEstimateQuality !== 'approx' || supplyEstimateDays === null) {
        return null;
    }

    const days = Math.max(0, supplyEstimateDays);
    const capped = Math.min(days, MEDICATION_SUPPLY_PROGRESS_FULL_DAYS);

    return Math.round((capped / MEDICATION_SUPPLY_PROGRESS_FULL_DAYS) * 100);
}
