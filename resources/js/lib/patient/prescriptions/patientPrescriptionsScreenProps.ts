import type { MedicationPrescriptionListItem, Paginated } from '@/lib/types';

export type PatientPrescriptionMedicationChoice = {
    id: number;
    name: string;
    type_medication: string;
};

export type PatientPrescriptionsScreenProps = {
    prescriptions: Paginated<MedicationPrescriptionListItem>;
    medication_choices: PatientPrescriptionMedicationChoice[];
};
