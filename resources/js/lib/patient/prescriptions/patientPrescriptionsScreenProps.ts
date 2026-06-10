import type { MedicationPrescriptionItem, Paginated } from '@/lib/types';

export type PatientPrescriptionMedicationChoice = {
    id: number;
    name: string;
    type_medication: string;
};

export type PatientPrescriptionsScreenProps = {
    prescriptions?: Paginated<MedicationPrescriptionItem>;
    medication_choices?: PatientPrescriptionMedicationChoice[];
};
