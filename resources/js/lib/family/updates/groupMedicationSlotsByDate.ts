import type { MedicationIntakeHistorySlot } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';

export type MedicationSlotsByDate = {
    date: string;
    slots: MedicationIntakeHistorySlot[];
};

export function groupMedicationSlotsByDate(
    slots: MedicationIntakeHistorySlot[],
): MedicationSlotsByDate[] {
    const slotsByDate = new Map<string, MedicationIntakeHistorySlot[]>();

    for (const slot of slots) {
        const existing = slotsByDate.get(slot.intake_date) ?? [];

        existing.push(slot);
        slotsByDate.set(slot.intake_date, existing);
    }

    const seenDates = new Set<string>();
    const groups: MedicationSlotsByDate[] = [];

    for (const slot of slots) {
        if (seenDates.has(slot.intake_date)) {
            continue;
        }

        seenDates.add(slot.intake_date);

        groups.push({
            date: slot.intake_date,
            slots: slotsByDate.get(slot.intake_date) ?? [],
        });
    }

    return groups;
}
