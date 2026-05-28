<?php

declare(strict_types=1);

namespace App\Services\Medications\MedicationPlanProposals;

use App\Enums\MedicationPlanProposalStatus;
use App\Enums\SecurityActivityDescription;
use App\Mail\MedicationPlanProposalMail;
use App\Models\MedicationPlanProposal;
use App\Models\User;
use App\Services\Audit\SecurityActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

final class MedicationPlanProposalPublishService
{
    public function __construct(
        private readonly SecurityActivityLogger $securityActivityLogger,
    ) {}

    public function publish(
        MedicationPlanProposal $proposal,
        User $publishedBy,
        string $patientEmail,
    ): MedicationPlanProposal {
        if (! $proposal->isDraft()) {
            throw ValidationException::withMessages([
                'proposal' => [trans('medication_plan_proposal.publish.not_draft')],
            ]);
        }

        $normalizedEmail = User::normalizeEmail($patientEmail);
        $emailHash = User::hashEmail($normalizedEmail);
        $tokenHash = hash('sha256', bin2hex(random_bytes(20)));
        $expiresAt = now()->addDays((int) config('services.medication_plan_proposal.expiry_days', 14));

        DB::transaction(function () use ($proposal, $normalizedEmail, $emailHash, $tokenHash, $expiresAt): void {
            MedicationPlanProposal::query()
                ->whereKey($proposal->id)
                ->lockForUpdate()
                ->firstOrFail();

            $proposal->forceFill([
                'status' => MedicationPlanProposalStatus::PUBLISHED,
                'invited_patient_email' => $normalizedEmail,
                'invited_patient_email_hash' => $emailHash,
                'token_hash' => $tokenHash,
                'expires_at' => $expiresAt,
                'published_at' => now(),
                'revoked_at' => null,
                'declined_at' => null,
            ])->save();
        });

        $proposal->load('items');
        $medicationName = (string) ($proposal->items->first()?->name ?? trans('medication_plan_proposal.unnamed_medication'));

        try {
            Mail::to($normalizedEmail)->send(new MedicationPlanProposalMail(
                expiresAt: $expiresAt,
                medicationName: $medicationName,
                familyPageUrl: route('patient.family', absolute: true),
            ));
        } catch (\Throwable $e) {
            report($e);

            $proposal->forceFill([
                'status' => MedicationPlanProposalStatus::DRAFT,
                'invited_patient_email' => null,
                'invited_patient_email_hash' => null,
                'token_hash' => null,
                'expires_at' => null,
                'published_at' => null,
            ])->save();

            throw ValidationException::withMessages([
                'patient_email' => [trans('medication_plan_proposal.publish.mail_failed')],
            ]);
        }

        $this->securityActivityLogger->record(
            SecurityActivityDescription::MEDICATION_PLAN_PROPOSAL_PUBLISHED,
            causer: $publishedBy,
            subject: $proposal,
            properties: [
                'patient_id' => $proposal->patient_id,
                'family_id' => $proposal->family_id,
                'invited_patient_email_hash' => $emailHash,
            ],
        );

        return $proposal->fresh();
    }

    public function revoke(MedicationPlanProposal $proposal, User $revokedBy): void
    {
        if (! $proposal->isPublished()) {
            return;
        }

        $proposal->forceFill([
            'status' => MedicationPlanProposalStatus::REVOKED,
            'revoked_at' => now(),
        ])->save();

        $this->securityActivityLogger->record(
            SecurityActivityDescription::MEDICATION_PLAN_PROPOSAL_REVOKED,
            causer: $revokedBy,
            subject: $proposal,
            properties: [
                'patient_id' => $proposal->patient_id,
                'family_id' => $proposal->family_id,
            ],
        );
    }
}
