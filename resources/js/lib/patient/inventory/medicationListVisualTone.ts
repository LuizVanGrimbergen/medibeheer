import type {
    MedicationListItem,
    MedicationSupplyEstimateQuality,
} from '@/lib/types';

import {
    MEDICATION_SUPPLY_CRITICAL_MAX_DAYS,
    MEDICATION_SUPPLY_WARNING_MAX_DAYS,
} from './medicationSupplyDayThresholds';

export type MedicationStockProgressTone = 'critical' | 'warning' | 'safe';

export type MedicationVisualToneContext = {
    supply_estimate_days: number | null;
    supply_estimate_quality: MedicationSupplyEstimateQuality;
};

export function medicationSupplyProgressToneFromDays(
    days: number,
): MedicationStockProgressTone {
    if (days <= MEDICATION_SUPPLY_CRITICAL_MAX_DAYS) {
        return 'critical';
    }

    if (days <= MEDICATION_SUPPLY_WARNING_MAX_DAYS) {
        return 'warning';
    }

    return 'safe';
}

/** Card tone from supply days when known. */
export function medicationVisualToneFromContext(
    context: MedicationVisualToneContext,
): MedicationStockProgressTone | null {
    if (
        context.supply_estimate_quality === 'approx'
        && context.supply_estimate_days !== null
    ) {
        return medicationSupplyProgressToneFromDays(context.supply_estimate_days);
    }

    return null;
}

export function medicationListVisualTone(
    medication: MedicationListItem,
): MedicationStockProgressTone | null {
    return medicationVisualToneFromContext(medication);
}
