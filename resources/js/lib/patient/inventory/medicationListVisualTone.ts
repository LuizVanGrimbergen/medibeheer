import type { MedicationStockProgressTone } from '@/lib/patient/inventory/medicationStockProgressTone';
import { medicationStockProgressTone } from '@/lib/patient/inventory/medicationStockProgressTone';
import type { MedicationListItem } from '@/lib/types';

const SUPPLY_CRITICAL_MAX_DAYS = 5;

const SUPPLY_WARNING_MAX_DAYS = 7;

/** Card tone from supply days when known; otherwise stock vs low threshold. */
export function medicationListVisualTone(
    medication: MedicationListItem,
): MedicationStockProgressTone | null {
    if (
        medication.supply_estimate_quality === 'approx'
        && medication.supply_estimate_days !== null
    ) {
        const days = medication.supply_estimate_days;

        if (days <= SUPPLY_CRITICAL_MAX_DAYS) {
            return 'critical';
        }

        if (days <= SUPPLY_WARNING_MAX_DAYS) {
            return 'warning';
        }

        return 'safe';
    }

    const stock = medication.stocks[0];

    if (stock === undefined) {
        return null;
    }

    return medicationStockProgressTone(
        stock.current_stock,
        stock.low_stock,
        medication.dose_unit,
    );
}
