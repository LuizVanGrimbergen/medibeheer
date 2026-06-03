import { medicationUrgencyProgressPercent } from '@/lib/patient/medications/urgency/medicationUrgencyTone';
import type { MedicationSupplyEstimateQuality } from '@/lib/types';


export function medicationStockProgressPercent(
    supplyEstimateDays: number | null,
    supplyEstimateQuality: MedicationSupplyEstimateQuality,
): number | null {
    if (supplyEstimateQuality !== 'approx' || supplyEstimateDays === null) {
        return null;
    }

    return medicationUrgencyProgressPercent(supplyEstimateDays);
}
