<?php

declare(strict_types=1);

namespace App\Services\Family;

use App\Models\Family;
use App\Models\FamilyInvitation;
use App\Models\Patient;
use App\Models\User;
use App\Services\Audit\SecurityActivityLogger;
use App\Services\Invitations\Concerns\HandlesPatientCareTeamInvitations;
use App\Services\Invitations\PatientCareTeamInvitationDefinition;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;

final class FamilyInvitationService
{
    use HandlesPatientCareTeamInvitations;

    public function __construct(
        private readonly SecurityActivityLogger $securityActivityLogger,
    ) {}

    public function create(Patient $patient, string $email, User $invitedBy): void
    {
        $this->createCareTeamInvitation($patient, $email, $invitedBy);
    }

    public function revoke(FamilyInvitation $invitation, User $revokedBy): void
    {
        $this->revokeCareTeamInvitation($invitation, $revokedBy);
    }

    public function acceptInvitation(User $user, FamilyInvitation $invitation): void
    {
        $this->acceptCareTeamInvitation($user, $invitation);
    }

    public function pendingIncomingForFamilyMember(User $user): Collection
    {
        return $this->pendingIncomingForInvitee($user);
    }

    protected function careTeamInvitationDefinition(): PatientCareTeamInvitationDefinition
    {
        return PatientCareTeamInvitationDefinition::family();
    }

    protected function securityActivityLogger(): SecurityActivityLogger
    {
        return $this->securityActivityLogger;
    }

    protected function userMayAcceptInvitation(User $user): bool
    {
        return $user->isFamilyMember();
    }

    /** @return SupportCollection<int, int|string> */
    protected function linkedPatientIdsForInvitee(User $user): SupportCollection
    {
        return $user->family?->patients()->pluck('patients.id') ?? collect();
    }

    protected function patientAlreadyLinkedToInviteeEmail(Patient $patient, string $normalizedEmail): bool
    {
        $emailHashes = User::emailHashCandidates($normalizedEmail);

        return $patient->families()
            ->whereHas('user', static fn ($query) => $query->whereIn('email_hash', $emailHashes))
            ->exists();
    }

    protected function linkInviteeProfileFromInvitation(User $user, Model $invitation): void
    {
        $family = Family::query()->firstOrCreate(
            ['user_id' => $user->id],
        );

        if ($family->patients()->whereKey($invitation->patient_id)->exists()) {
            $invitation->forceFill(['accepted_at' => now()])->save();

            return;
        }

        $family->patients()->syncWithoutDetaching([(int) $invitation->patient_id]);

        $invitation->forceFill(['accepted_at' => now()])->save();
    }
}
