<?php

namespace App\Policies;

use App\Enums\MedicationPlanProposalStatus;
use App\Models\MedicationPlanProposal;
use App\Models\User;

class MedicationPlanProposalPolicy
{
    public function create(User $user): bool
    {
        return $user->isFamilyMember();
    }

    public function update(User $user, MedicationPlanProposal $proposal): bool
    {
        if (! $this->familyOwnsProposal($user, $proposal)) {
            return false;
        }

        return $proposal->status === MedicationPlanProposalStatus::DRAFT;
    }

    public function publish(User $user, MedicationPlanProposal $proposal): bool
    {
        return $this->update($user, $proposal);
    }

    public function revoke(User $user, MedicationPlanProposal $proposal): bool
    {
        if (! $this->familyOwnsProposal($user, $proposal)) {
            return false;
        }

        return $proposal->status === MedicationPlanProposalStatus::PUBLISHED;
    }

    public function accept(User $user, MedicationPlanProposal $proposal): bool
    {
        return $this->patientCanRespondToProposal($user, $proposal);
    }

    public function decline(User $user, MedicationPlanProposal $proposal): bool
    {
        return $this->patientCanRespondToProposal($user, $proposal);
    }

    private function familyOwnsProposal(User $user, MedicationPlanProposal $proposal): bool
    {
        if (! $user->isFamilyMember()) {
            return false;
        }

        $family = $user->family;

        if ($family === null) {
            return false;
        }

        return (int) $family->id === (int) $proposal->family_id;
    }

    private function patientCanRespondToProposal(User $user, MedicationPlanProposal $proposal): bool
    {
        if (! $user->isPatient() || $user->patient === null) {
            return false;
        }

        if (! $proposal->isRedeemable()) {
            return false;
        }

        if ($proposal->patient_id !== null && ! $user->patient->is($proposal->patient)) {
            return false;
        }

        return $proposal->matchesInvitedUserEmail($user);
    }
}
