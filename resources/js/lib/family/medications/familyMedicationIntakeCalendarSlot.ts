import type {
    MedicationTypeValue,
    TodayMedicationIntakeDayPeriodValue,
} from '@/lib/types';

export type FamilyMedicationIntakeCalendarSlot = {
    medication_schedule_id: number;
    dose_time: string;
    snooze_minutes: number;
    day_period: TodayMedicationIntakeDayPeriodValue;
    name: string;
    type_medication: MedicationTypeValue;
    taken_at: string | null;
    intake_date: string;
};
