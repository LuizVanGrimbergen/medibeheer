export type FamilyMedicationPlanProposalSummary = {
    id: number;
    status: string;
    patient_name: string | null;
    medication_name: string | null;
    updated_at: string | null;
    can_edit: boolean;
    can_duplicate: boolean;
    can_publish: boolean;
    can_revoke: boolean;
    edit_url: string;
    duplicate_url: string;
    publish_url: string;
    revoke_url: string;
};
