import type { FamilyMedicationIntakeCalendarSlot } from '@/lib/family/medications/familyMedicationIntakeCalendarSlot';
import type { MedicationIntakeCalendarDay } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';
import type {
    FamilyDashboardProps,
    MedicationListItem,
    Paginated,
} from '@/lib/types';

export type FamilyMedicationsScreenProps = {
    family: FamilyDashboardProps;
    medications: Paginated<MedicationListItem>;
    medication_calendar_month: string;
    medication_calendar_days: MedicationIntakeCalendarDay[];
    medication_calendar_slots: FamilyMedicationIntakeCalendarSlot[];
};
