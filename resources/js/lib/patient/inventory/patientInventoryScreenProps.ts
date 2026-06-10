import type { MedicationRegisterItem, Paginated } from '@/lib/types';

export type PatientInventoryScreenProps = {
    medications?: Paginated<MedicationRegisterItem>;
};
