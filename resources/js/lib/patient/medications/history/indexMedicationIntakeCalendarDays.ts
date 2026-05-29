import type { MedicationIntakeCalendarDay } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';

export function indexMedicationIntakeCalendarDays(
    calendarDays: MedicationIntakeCalendarDay[],
): Record<string, MedicationIntakeCalendarDay> {
    const daysByDate: Record<string, MedicationIntakeCalendarDay> = {};

    for (const day of calendarDays) {
        daysByDate[day.date] = day;
    }

    return daysByDate;
}
