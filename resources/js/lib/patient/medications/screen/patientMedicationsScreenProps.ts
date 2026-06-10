import type { MedicationRegisterItem, Paginated } from '@/lib/types';

export type PatientMedicationsScreenProps = {
    active_medications?: Paginated<MedicationRegisterItem>;
    can_create_medication: boolean;
};
