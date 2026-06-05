import type { ComposerTranslation } from 'vue-i18n';
import { formatRemainingDaysStatusLine } from '@/lib/patient/medications/urgency/formatRemainingDaysStatusLine';
import type { MedicationSupplyEstimateQuality } from '@/lib/types';

export type MedicationSupplyEstimateSource = {
    supply_estimate_days: number | null;
    supply_estimate_quality: MedicationSupplyEstimateQuality;
};

const supplyEstimateStatusLineKeys = {
    lessThanDay: 'patient.inventory.supplyEstimateApproxLessThanDay',
    oneDay: 'patient.inventory.supplyEstimateApproxOneDay',
    days: 'patient.inventory.supplyEstimateApproxDays',
    durationUnits: {
        year: 'patient.duration.year',
        monthOne: 'patient.duration.monthOne',
        months: 'patient.duration.months',
        dayOne: 'patient.duration.dayOne',
        days: 'patient.duration.days',
    },
    durationStatusLine: 'patient.duration.supplyStatusLine',
} as const;

export function medicationSupplyEstimateLine(
    t: ComposerTranslation,
    source: MedicationSupplyEstimateSource,
): string {
    const days = source.supply_estimate_days;
    const quality = source.supply_estimate_quality;

    if (quality === 'approx' && days !== null) {
        return formatRemainingDaysStatusLine(
            t,
            days,
            supplyEstimateStatusLineKeys,
            'supply',
        );
    }

    return t('patient.inventory.supplyEstimateUnknown');
}
