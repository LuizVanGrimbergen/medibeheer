<?php

declare(strict_types=1);

namespace App\Services\Medications\MedicationPlanProposals;

use App\Enums\MedicationPlanProposalStatus;
use App\Enums\SecurityActivityDescription;
use App\Models\MedicationPlanProposal;
use App\Models\Patient;
use App\Models\User;
use App\Services\Audit\SecurityActivityLogger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class MedicationPlanProposalPatientService
{
    public function __construct(
        private readonly SecurityActivityLogger $securityActivityLogger,
        private readonly ImportMedicationPlanProposalToPatient $importer,
    ) {}

    public function pendingIncomingForPatient(User $user): Collection
    {
        if (! $user->isPatient() || $user->patient === null || $user->email_verified_at === null) {
            return new Collection;
        }

        $emailHashes = User::emailHashCandidates($user->email);

        if ($emailHashes === []) {
            return new Collection;
        }

        return MedicationPlanProposal::query()
            ->redeemable()
            ->whereIn('invited_patient_email_hash', $emailHashes, 'and', false)
            ->where(function ($query) use ($user): void {
                $patientId = $user->patient?->id;

                if ($patientId === null) {
                    return;
                }

                $query->whereNull('patient_id')
                    ->orWhere('patient_id', $patientId);
            })
            ->with(['items', 'family.user'])
            ->orderByDesc('published_at')
            ->get();
    }

    public function acceptedForPatient(User $user, int $limit = 3): Collection
    {
        if (! $user->isPatient() || $user->patient === null) {
            return new Collection;
        }

        return MedicationPlanProposal::query()
            ->where('status', MedicationPlanProposalStatus::ACCEPTED)
            ->where('patient_id', $user->patient->id)
            ->with(['items', 'family.user'])
            ->orderByDesc('accepted_at')
            ->limit($limit)
            ->get();
    }

    public function reviewProps(User $user, MedicationPlanProposal $proposal): array
    {
        if (! $proposal->isRedeemable()) {
            throw $this->unavailableException();
        }

        $this->authorizePatientMayReview($user, $proposal);

        $proposal->load(['items.schedule', 'family.user']);

        $item = $proposal->items->first();

        return [
            'proposal_id' => $proposal->id,
            'family_member_name' => (string) ($proposal->family?->user?->name ?? ''),
            'medication_name' => $item?->name,
            'dose' => $item?->dose,
            'dose_unit' => $item?->dose_unit?->value,
            'strength' => $item?->strength,
            'note' => $item?->note,
            'current_stock' => $item?->current_stock,
        ];
    }

    public function acceptProposal(User $user, MedicationPlanProposal $proposal): MedicationPlanProposal
    {
        $patient = $user->patient;

        if ($patient === null) {
            throw $this->unavailableException();
        }

        return DB::transaction(function () use ($user, $proposal, $patient): MedicationPlanProposal {
            $lockedProposal = $this->findLockablePublishedProposal($proposal, $patient);

            $this->authorizePatientMayReview($user, $lockedProposal);

            $lockedProposal->load(['items.schedule.weekdays', 'family']);

            $family = $lockedProposal->family;

            if ($family !== null) {
                $family->patients()->syncWithoutDetaching([(int) $patient->id]);
            }

            $this->importer->import($patient, $lockedProposal);

            $lockedProposal->forceFill([
                'status' => MedicationPlanProposalStatus::ACCEPTED,
                'patient_id' => $patient->id,
                'accepted_at' => now(),
            ])->save();

            $this->securityActivityLogger->record(
                SecurityActivityDescription::MEDICATION_PLAN_PROPOSAL_REDEEMED,
                causer: $user,
                subject: $lockedProposal,
                properties: [
                    'patient_id' => $patient->id,
                    'family_id' => $lockedProposal->family_id,
                ],
            );

            return $lockedProposal;
        });
    }

    public function declineProposal(User $user, MedicationPlanProposal $proposal): MedicationPlanProposal
    {
        $patient = $user->patient;

        if ($patient === null) {
            throw $this->unavailableException();
        }

        return DB::transaction(function () use ($user, $proposal, $patient): MedicationPlanProposal {
            $lockedProposal = $this->findLockablePublishedProposal($proposal, $patient);

            $this->authorizePatientMayReview($user, $lockedProposal);

            $lockedProposal->forceFill([
                'status' => MedicationPlanProposalStatus::DECLINED,
                'patient_id' => $patient->id,
                'declined_at' => now(),
            ])->save();

            $this->securityActivityLogger->record(
                SecurityActivityDescription::MEDICATION_PLAN_PROPOSAL_DECLINED,
                causer: $user,
                subject: $lockedProposal,
                properties: [
                    'patient_id' => $patient->id,
                    'family_id' => $lockedProposal->family_id,
                ],
            );

            return $lockedProposal;
        });
    }

    private function findLockablePublishedProposal(
        MedicationPlanProposal $proposal,
        Patient $patient,
    ): MedicationPlanProposal {
        $lockedProposal = MedicationPlanProposal::query()
            ->whereKey($proposal->id)
            ->redeemable()
            ->where(function ($query) use ($patient): void {
                $query->whereNull('patient_id')
                    ->orWhere('patient_id', $patient->id);
            })
            ->lockForUpdate()
            ->first();

        if ($lockedProposal === null) {
            throw $this->unavailableException();
        }

        return $lockedProposal;
    }

    private function authorizePatientMayReview(User $user, MedicationPlanProposal $proposal): void
    {
        if (! $user->isPatient() || $user->patient === null) {
            throw $this->unavailableException();
        }

        if ($proposal->patient_id !== null && ! $user->patient->is($proposal->patient)) {
            throw $this->unavailableException();
        }

        if (! $proposal->matchesInvitedUserEmail($user)) {
            throw $this->unavailableException();
        }
    }

    private function unavailableException(): ValidationException
    {
        return ValidationException::withMessages([
            'proposal' => [trans('medication_plan_proposal.review.unavailable')],
        ]);
    }
}
