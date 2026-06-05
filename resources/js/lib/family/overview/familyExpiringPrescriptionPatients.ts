export type FamilyExpiringPrescription = {
    id: number;
    medication_id: number;
    medication_name: string;
    days_remaining: number;
    is_last_in_batch: boolean;
};

export type FamilyExpiringPrescriptionPatient = {
    patient_id: number;
    patient_name: string;
    switch_url: string;
    medications_url: string;
    prescriptions: FamilyExpiringPrescription[];
};
