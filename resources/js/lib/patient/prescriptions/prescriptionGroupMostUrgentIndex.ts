import { prescriptionExpiryUrgencyContext } from '@/lib/patient/prescriptions/prescriptionExpiryUrgency';
import type { MedicationPrescriptionGroupPrescriptionItem } from '@/lib/types';

export function prescriptionGroupMostUrgentIndex(
    prescriptions: MedicationPrescriptionGroupPrescriptionItem[],
): number | null {
    let mostUrgentIndex: number | null = null;
    let fewestDaysRemaining = Number.POSITIVE_INFINITY;

    for (const [index, prescription] of prescriptions.entries()) {
        const context = prescriptionExpiryUrgencyContext(
            prescription.prescription_expiry_date,
        );

        if (context === null) {
            continue;
        }

        if (context.days_remaining < fewestDaysRemaining) {
            fewestDaysRemaining = context.days_remaining;
            mostUrgentIndex = index;
        }
    }

    return mostUrgentIndex;
}
