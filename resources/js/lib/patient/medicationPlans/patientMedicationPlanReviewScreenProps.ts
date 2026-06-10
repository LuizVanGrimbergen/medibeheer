export type PatientMedicationPlanReviewScreenProps = {
    proposal_id: number;
    family_member_name: string;
    medication_name: string | null;
    dose: string | null;
    dose_unit: string | null;
    strength: string | null;
    note: string | null;
    current_stock: string | null;
};
