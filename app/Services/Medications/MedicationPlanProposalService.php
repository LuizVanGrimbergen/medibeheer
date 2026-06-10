<?php

declare(strict_types=1);

namespace App\Services\Medications;

use App\Models\Family;
use App\Models\MedicationPlanProposal;
use App\Models\User;
use App\Services\Medications\MedicationPlanProposals\MedicationPlanProposalDraftService;
use App\Services\Medications\MedicationPlanProposals\MedicationPlanProposalPatientService;
use App\Services\Medications\MedicationPlanProposals\MedicationPlanProposalPublishService;
use Illuminate\Database\Eloquent\Collection;

final class MedicationPlanProposalService
{
    public function __construct(
        private readonly MedicationPlanProposalDraftService $draftService,
        private readonly MedicationPlanProposalPublishService $publishService,
        private readonly MedicationPlanProposalPatientService $patientService,
    ) {}

    public function createDraft(Family $family, array $validated): MedicationPlanProposal
    {
        return $this->draftService->createDraft($family, $validated);
    }

    public function addDraftItem(MedicationPlanProposal $proposal, array $validated): MedicationPlanProposal
    {
        return $this->draftService->addDraftItem($proposal, $validated);
    }

    public function updateDraft(
        MedicationPlanProposal $proposal,
        array $validated,
        ?int $itemId = null,
    ): MedicationPlanProposal {
        return $this->draftService->updateDraft($proposal, $validated, $itemId);
    }

    public function publish(
        MedicationPlanProposal $proposal,
        User $publishedBy,
        string $patientEmail,
    ): MedicationPlanProposal {
        return $this->publishService->publish($proposal, $publishedBy, $patientEmail);
    }

    public function revoke(MedicationPlanProposal $proposal, User $revokedBy): void
    {
        $this->publishService->revoke($proposal, $revokedBy);
    }

    public function pendingIncomingForPatient(User $user): Collection
    {
        return $this->patientService->pendingIncomingForPatient($user);
    }

    public function acceptedForPatient(User $user, int $limit = 3): Collection
    {
        return $this->patientService->acceptedForPatient($user, $limit);
    }

    public function reviewProps(User $user, MedicationPlanProposal $proposal): array
    {
        return $this->patientService->reviewProps($user, $proposal);
    }

    public function acceptProposal(User $user, MedicationPlanProposal $proposal): MedicationPlanProposal
    {
        return $this->patientService->acceptProposal($user, $proposal);
    }

    public function declineProposal(User $user, MedicationPlanProposal $proposal): MedicationPlanProposal
    {
        return $this->patientService->declineProposal($user, $proposal);
    }
}
