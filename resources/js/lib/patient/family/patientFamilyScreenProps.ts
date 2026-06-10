import type {
    AcceptedMedicationPlanProposal,
    LinkedCareTeamMember,
    PendingCareTeamInvitation,
    PendingMedicationPlanProposal,
} from '@/lib/types';

export type PatientFamilyScreenProps = {
    family_invitations?: PendingCareTeamInvitation[];
    pending_medication_plans?: PendingMedicationPlanProposal[];
    accepted_medication_plans?: AcceptedMedicationPlanProposal[];
    family_invitation_store_url?: string;
    doctor_invitations?: PendingCareTeamInvitation[];
    linked_doctors?: LinkedCareTeamMember[];
    linked_family_members?: LinkedCareTeamMember[];
    doctor_invitation_store_url?: string;
};
