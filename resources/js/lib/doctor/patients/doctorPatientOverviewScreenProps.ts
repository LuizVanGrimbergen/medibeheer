import type { DailyCheckin } from '@/lib/types';
import type {
    MedicationIntakeCalendarDay,
    MedicationIntakeHistorySlot,
} from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';

export type DoctorPatientOverviewScreenProps = {
    selected_patient: {
        public_id: string;
        name: string;
    };
    medication_calendar_month: string;
    medication_calendar_days: MedicationIntakeCalendarDay[];
    medication_calendar_slots: MedicationIntakeHistorySlot[];
    wellbeing_calendar_month: string;
    wellbeing_calendar_checkins: DailyCheckin[];
};
