import type { MedicationIntakeHistorySlot } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';
import { compareTodayMedicationIntakeSlots } from '@/lib/patient/medications/todayMedicationIntakeDayPeriod';

export type MedicationIntakesByDate = {
    date: string;
    intakes: MedicationIntakeHistorySlot[];
};

export function groupMedicationIntakesByDate(
    intakes: MedicationIntakeHistorySlot[],
): MedicationIntakesByDate[] {
    const map = new Map<string, MedicationIntakeHistorySlot[]>();

    for (const intake of intakes) {
        const existing = map.get(intake.intake_date) ?? [];

        existing.push(intake);
        map.set(intake.intake_date, existing);
    }

    return [...map.entries()]
        .map(([date, dayIntakes]) => ({
            date,
            intakes: [...dayIntakes].sort(compareTodayMedicationIntakeSlots),
        }))
        .sort((left, right) => right.date.localeCompare(left.date));
}
