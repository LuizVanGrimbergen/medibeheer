export type DoctorPatientUrgentPrescription = {
    id: number;
    medication_id: number;
    medication_name: string;
    days_remaining: number;
    is_last_in_batch: boolean;
};
