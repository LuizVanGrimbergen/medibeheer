import type { MedicationListItem, Paginated } from '@/lib/types';

export type PatientMedicationsScreenProps = {
    active_medications?: Paginated<MedicationListItem>;
    can_create_medication: boolean;
};
