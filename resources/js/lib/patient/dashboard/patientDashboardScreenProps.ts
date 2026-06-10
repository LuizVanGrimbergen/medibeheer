import type { DailyCheckin, TodayMedicationIntakeSlot } from '@/lib/types';

export type PatientDashboardScreenProps = {
    today_date: string;
    today_checkin?: DailyCheckin | null;
    today_medication_intakes?: TodayMedicationIntakeSlot[];
    pending_push_medication_mark: string | null;
    has_medications?: boolean;
    can_create_medication?: boolean;
};
