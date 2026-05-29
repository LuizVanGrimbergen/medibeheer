import type { MedicationListItem, Paginated } from '@/lib/types';

export type PatientInventoryScreenProps = {
    medications: Paginated<MedicationListItem>;
};
