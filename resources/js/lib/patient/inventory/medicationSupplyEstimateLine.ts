import type { ComposerTranslation } from 'vue-i18n';
import type { MedicationSupplyEstimateQuality } from '@/lib/types';

export type MedicationSupplyEstimateSource = {
    supply_estimate_days: number | null;
    supply_estimate_quality: MedicationSupplyEstimateQuality;
};

export function medicationSupplyEstimateLine(
    t: ComposerTranslation,
    source: MedicationSupplyEstimateSource,
): string {
    const days = source.supply_estimate_days;
    const quality = source.supply_estimate_quality;

    if (quality === 'approx' && days !== null) {
        if (days < 1) {
            return t('patient.inventory.supplyEstimateApproxLessThanDay');
        }

        if (days === 1) {
            return t('patient.inventory.supplyEstimateApproxOneDay');
        }

        return t('patient.inventory.supplyEstimateApproxDays', {
            days: String(days),
        });
    }

    return t('patient.inventory.supplyEstimateUnknown');
}
