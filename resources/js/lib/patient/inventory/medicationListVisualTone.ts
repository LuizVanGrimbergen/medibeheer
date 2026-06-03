import {
    medicationUrgencyToneFromDaysRemaining
    
} from '@/lib/patient/medications/urgency/medicationUrgencyTone';
import type {MedicationUrgencyTone} from '@/lib/patient/medications/urgency/medicationUrgencyTone';
import type {
    MedicationListItem,
    MedicationSupplyEstimateQuality,
} from '@/lib/types';


export type MedicationStockProgressTone = MedicationUrgencyTone;

export type MedicationVisualToneContext = {
    supply_estimate_days: number | null;
    supply_estimate_quality: MedicationSupplyEstimateQuality;
};

export { medicationUrgencyToneFromDaysRemaining as medicationSupplyProgressToneFromDays };

/** Card tone from supply days when known. */
export function medicationVisualToneFromContext(
    context: MedicationVisualToneContext,
): MedicationStockProgressTone | null {
    if (
        context.supply_estimate_quality === 'approx'
        && context.supply_estimate_days !== null
    ) {
        return medicationUrgencyToneFromDaysRemaining(context.supply_estimate_days);
    }

    return null;
}

export function medicationListVisualTone(
    medication: MedicationListItem,
): MedicationStockProgressTone | null {
    if (medication.list_status !== 'active') {
        return null;
    }

    return medicationVisualToneFromContext(medication);
}
