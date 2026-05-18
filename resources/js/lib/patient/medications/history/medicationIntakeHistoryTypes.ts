import type {
    TodayMedicationIntakeDayPeriodValue,
    TodayMedicationIntakeSlot,
} from '@/lib/types';

export const MEDICATION_INTAKE_DAY_STATUS_VALUES = [
    'no_schedule',
    'none_taken',
    'partial',
    'complete',
] as const;

export type MedicationIntakeDayStatusValue =
    (typeof MEDICATION_INTAKE_DAY_STATUS_VALUES)[number];

export type MedicationIntakeCalendarDay = {
    date: string;
    status: MedicationIntakeDayStatusValue;
    scheduled_count: number;
    taken_count: number;
};

export type MedicationIntakeHistorySlot = TodayMedicationIntakeSlot & {
    intake_date: string;
};

export function isMedicationIntakeDayStatusValue(
    value: string,
): value is MedicationIntakeDayStatusValue {
    return (MEDICATION_INTAKE_DAY_STATUS_VALUES as readonly string[]).includes(value);
}

export type { TodayMedicationIntakeDayPeriodValue };
