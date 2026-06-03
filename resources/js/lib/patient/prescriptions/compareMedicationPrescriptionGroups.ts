import type { MedicationPrescriptionGroupListItem } from '@/lib/types';

function soonestExpirySortKey(
    group: MedicationPrescriptionGroupListItem,
): string | null {
    const dates = group.prescriptions
        .map((prescription) => prescription.prescription_expiry_date?.trim() ?? '')
        .filter((date) => date.length > 0)
        .sort((left, right) => left.localeCompare(right));

    return dates[0] ?? null;
}

/** Soonest expiry in the group first; groups without dates last (alphabetically by medication name). */
export function compareMedicationPrescriptionGroups(
    left: MedicationPrescriptionGroupListItem,
    right: MedicationPrescriptionGroupListItem,
): number {
    const leftKey = soonestExpirySortKey(left);
    const rightKey = soonestExpirySortKey(right);

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
