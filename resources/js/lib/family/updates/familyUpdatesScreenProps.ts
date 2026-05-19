import type { DailyCheckin } from '@/lib/types';
import type { MedicationIntakeHistorySlot } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';

export type FamilyUpdatesPeriodDays = 1 | 3 | 5;

export type FamilyUpdatesScreenProps = {
    updates_period_days: FamilyUpdatesPeriodDays;
    updates_checkins: DailyCheckin[];
    updates_medication_slots: MedicationIntakeHistorySlot[];
};
