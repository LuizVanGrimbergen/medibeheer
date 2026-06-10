import type { MedicationPrescriptionItem } from '@/lib/types';

function prescriptionExpirySortKey(
    prescription: MedicationPrescriptionItem,
): string | null {
    const trimmed = prescription.prescription_expiry_date?.trim() ?? '';

    if (trimmed.length < 1) {
        return null;
    }

    return trimmed;
}

/** Soonest expiry first; prescriptions without a date last (alphabetically by medication name). */
export function compareMedicationPrescriptionItems(
    left: MedicationPrescriptionItem,
    right: MedicationPrescriptionItem,
): number {
    const leftKey = prescriptionExpirySortKey(left);
    const rightKey = prescriptionExpirySortKey(right);

    if (leftKey === null && rightKey === null) {
        return left.medication.name.localeCompare(right.medication.name, 'nl');
    }

    if (leftKey === null) {
        return 1;
    }

    if (rightKey === null) {
        return -1;
    }

    const byDate = leftKey.localeCompare(rightKey);

    if (byDate !== 0) {
        return byDate;
    }

    return left.medication.name.localeCompare(right.medication.name, 'nl');
}
