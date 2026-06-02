import type { MedicationUrgencyTone } from '@/lib/patient/medications/urgency/medicationUrgencyTone';
import { prescriptionExpiryUrgencyContext } from '@/lib/patient/prescriptions/prescriptionExpiryUrgency';
import type { MedicationPrescriptionGroupPrescriptionItem } from '@/lib/types';

const URGENCY_TONE_RANK: Record<MedicationUrgencyTone, number> = {
    critical: 0,
    warning: 1,
    safe: 2,
};

export function prescriptionGroupUrgencyTone(
    prescriptions: MedicationPrescriptionGroupPrescriptionItem[],
): MedicationUrgencyTone | null {
    let mostUrgentTone: MedicationUrgencyTone | null = null;
    let mostUrgentRank = Number.POSITIVE_INFINITY;

    for (const prescription of prescriptions) {
        const context = prescriptionExpiryUrgencyContext(
            prescription.prescription_expiry_date,
        );

        if (context === null) {
            continue;
        }

        const rank = URGENCY_TONE_RANK[context.tone];

        if (rank < mostUrgentRank) {
            mostUrgentRank = rank;
            mostUrgentTone = context.tone;
        }
    }

    return mostUrgentTone;
}
