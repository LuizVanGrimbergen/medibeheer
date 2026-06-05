import type { MedicationIntakeDayIconStatusValue } from '@/lib/patient/medications/history/medicationIntakeDayPresentation';
import type {
    MedicationIntakeCalendarDay,
    MedicationIntakeHistorySlot,
} from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';

export function filterDoctorPatientMedicationSlots(
    slots: MedicationIntakeHistorySlot[],
    calendarDays: MedicationIntakeCalendarDay[],
    status: MedicationIntakeDayIconStatusValue | null,
): MedicationIntakeHistorySlot[] {
    if (status === null) {
        return slots;
    }

    const dayStatusByDate = new Map(
        calendarDays.map((day) => [day.date, day.status]),
    );

    return slots.filter(
        (slot) => dayStatusByDate.get(slot.intake_date) === status,
    );
}
