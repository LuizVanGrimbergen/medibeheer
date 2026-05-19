import type { MedicationIntakeHistorySlot } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';
import type { DailyCheckin } from '@/lib/types';

export type FamilyUpdatesPeriodDays = 1 | 3 | 5;

export type FamilyUpdatesScreenProps = {
    updates_period_days: FamilyUpdatesPeriodDays;
    updates_checkins: DailyCheckin[];
    updates_medication_intakes: MedicationIntakeHistorySlot[];
};

export type DailyCheckinCreatedBroadcastPayload = {
    patient_id: number;
    checkin_id: number;
    checkin_date: string;
};

export type MedicationIntakeRecordedBroadcastPayload = {
    patient_id: number;
    intake_id: number;
    intake_date: string;
};
