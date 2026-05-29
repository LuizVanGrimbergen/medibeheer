<?php

declare(strict_types=1);

namespace App\Services\Doctor;

use App\Models\Doctor;
use App\Models\DoctorInvitation;
use App\Models\Patient;
use App\Models\User;
use App\Services\Audit\SecurityActivityLogger;
use App\Services\Invitations\Concerns\HandlesPatientCareTeamInvitations;
use App\Services\Invitations\PatientCareTeamInvitationDefinition;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;

final class DoctorInvitationService
{
    use HandlesPatientCareTeamInvitations;

    public function __construct(
        private readonly SecurityActivityLogger $securityActivityLogger,
    ) {}

    public function create(Patient $patient, string $email, User $invitedBy): void
    {
        $this->createCareTeamInvitation($patient, $email, $invitedBy);
    }

    public function revoke(DoctorInvitation $invitation, User $revokedBy): void
    {
        $this->revokeCareTeamInvitation($invitation, $revokedBy);
    }

    public function acceptInvitation(User $user, DoctorInvitation $invitation): void
    {
        $this->acceptCareTeamInvitation($user, $invitation);
    }

    /** @return Collection<int, DoctorInvitation> */
    public function pendingIncomingForDoctor(User $user): Collection
    {
        return $this->pendingIncomingForInvitee($user);
    }

    protected function careTeamInvitationDefinition(): PatientCareTeamInvitationDefinition
    {
        return PatientCareTeamInvitationDefinition::doctor();
    }

    protected function securityActivityLogger(): SecurityActivityLogger
    {
        return $this->securityActivityLogger;
    }

    protected function userMayAcceptInvitation(User $user): bool
    {
        return $user->isDoctor();
    }

    /** @return SupportCollection<int, int|string> */
    protected function linkedPatientIdsForInvitee(User $user): SupportCollection
    {
        return $user->doctor?->patients()->pluck('patients.id') ?? collect();
    }

    protected function patientAlreadyLinkedToInviteeEmail(Patient $patient, string $normalizedEmail): bool
    {
        $emailHashes = User::emailHashCandidates($normalizedEmail);

        return $patient->doctors()
            ->whereHas('user', static fn ($query) => $query->whereIn('email_hash', $emailHashes))
            ->exists();
    }

    protected function linkInviteeProfileFromInvitation(User $user, Model $invitation): void
    {
        /** @var DoctorInvitation $invitation */
        $doctor = Doctor::query()->firstOrCreate(
            ['user_id' => $user->id],
        );

        if ($doctor->patients()->whereKey($invitation->patient_id)->exists()) {
            $invitation->forceFill(['accepted_at' => now()])->save();

            return;
        }

        $doctor->patients()->syncWithoutDetaching([(int) $invitation->patient_id]);

        $invitation->forceFill(['accepted_at' => now()])->save();
    }
}
