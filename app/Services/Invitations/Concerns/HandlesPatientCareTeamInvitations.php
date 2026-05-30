<?php

declare(strict_types=1);

namespace App\Services\Invitations\Concerns;

use App\Models\DoctorInvitation;
use App\Models\FamilyInvitation;
use App\Models\Patient;
use App\Models\User;
use App\Services\Audit\SecurityActivityLogger;
use App\Services\Invitations\PatientCareTeamInvitationDefinition;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

trait HandlesPatientCareTeamInvitations
{
    abstract protected function careTeamInvitationDefinition(): PatientCareTeamInvitationDefinition;

    abstract protected function userMayAcceptInvitation(User $user): bool;

    abstract protected function linkedPatientIdsForInvitee(User $user): Collection;

    abstract protected function patientAlreadyLinkedToInviteeEmail(Patient $patient, string $normalizedEmail): bool;

    abstract protected function linkInviteeProfileFromInvitation(User $user, Model $invitation): void;

    abstract protected function securityActivityLogger(): SecurityActivityLogger;

    public function createCareTeamInvitation(Patient $patient, string $email, User $invitedBy): void
    {
        $definition = $this->careTeamInvitationDefinition();
        $normalized = User::normalizeEmail($email);
        $emailHash = User::hashEmail($normalized);
        $expiresAt = now()->addDays((int) config("services.{$definition->configKey}.expiry_days", 14));
        $invitationModelClass = $definition->invitationModelClass;

        $invitation = DB::transaction(function () use (
            $patient,
            $normalized,
            $emailHash,
            $expiresAt,
            $definition,
            $invitationModelClass,
        ): Model {
            Patient::query()->whereKey($patient->id)->lockForUpdate()->firstOrFail();

            if ($this->patientHasPendingInvitationForEmail($patient, $emailHash, lockForUpdate: true)) {
                throw ValidationException::withMessages([
                    'email' => [trans("{$definition->translationKey}.validation.duplicate_pending")],
                ]);
            }

            if ($this->patientAlreadyLinkedToInviteeEmail($patient, $normalized)) {
                throw ValidationException::withMessages([
                    'email' => [trans("{$definition->translationKey}.validation.already_linked")],
                ]);
            }

            return $invitationModelClass::create([
                'patient_id' => $patient->id,
                'invited_email' => $normalized,
                'invited_email_hash' => $emailHash,
                'expires_at' => $expiresAt,
            ]);
        });

        try {
            Mail::to($normalized)->sendNow(new $definition->invitationMailableClass(
                expiresAt: $expiresAt,
                patientName: (string) $patient->user->name,
            ));
        } catch (\Throwable $e) {
            report($e);

            $invitation->delete();

            throw ValidationException::withMessages([
                'email' => [trans("{$definition->translationKey}.flash.mail_failed")],
            ]);
        }

        $this->securityActivityLogger()->record(
            $definition->createdActivity,
            causer: $invitedBy,
            subject: $invitation,
            properties: [
                'patient_id' => $patient->id,
                'invited_email_hash' => $emailHash,
            ],
        );
    }

    public function revokeCareTeamInvitation(Model $invitation, User $revokedBy): void
    {
        if ($invitation->accepted_at !== null || $invitation->revoked_at !== null) {
            return;
        }

        $invitation->forceFill(['revoked_at' => now()])->save();

        $definition = $this->careTeamInvitationDefinition();

        $this->securityActivityLogger()->record(
            $definition->revokedActivity,
            causer: $revokedBy,
            subject: $invitation,
            properties: [
                'patient_id' => $invitation->patient_id,
            ],
        );
    }

    public function acceptCareTeamInvitation(User $user, Model $invitation): void
    {
        $this->ensureUserMayAcceptInvitation($user);

        $definition = $this->careTeamInvitationDefinition();
        $invitationModelClass = $definition->invitationModelClass;

        DB::transaction(function () use ($user, $invitation, $invitationModelClass): void {
            $lockedInvitation = $invitationModelClass::query()
                ->whereKey($invitation->id)
                ->pending()
                ->lockForUpdate()
                ->first();

            if ($lockedInvitation === null) {
                throw $this->invalidInvitationException();
            }

            if (! $this->invitationMatchesUserEmail($user, $lockedInvitation)) {
                throw $this->invalidInvitationException();
            }

            $this->acceptLockedInvitation($user, $lockedInvitation);
        });
    }

    public function pendingIncomingForInvitee(User $user): EloquentCollection
    {
        $definition = $this->careTeamInvitationDefinition();
        $invitationModelClass = $definition->invitationModelClass;

        if (! $this->userMayAcceptInvitation($user) || $user->email_verified_at === null) {
            return $invitationModelClass::query()->whereKey(-1)->get();
        }

        if ($user->email === null || $user->email === '') {
            return $invitationModelClass::query()->whereKey(-1)->get();
        }

        $emailHashes = User::emailHashCandidates($user->email);

        if ($emailHashes === []) {
            return $invitationModelClass::query()->whereKey(-1)->get();
        }

        $linkedPatientIds = $this->linkedPatientIdsForInvitee($user);

        return $invitationModelClass::query()
            ->whereIn('invited_email_hash', $emailHashes, 'and', false)
            ->pending()
            ->when(
                $linkedPatientIds->isNotEmpty(),
                static fn ($query) => $query->whereNotIn('patient_id', $linkedPatientIds),
            )
            ->with('patient.user')
            ->orderByDesc('created_at')
            ->get();
    }

    private function invalidInvitationException(): ValidationException
    {
        $translationKey = $this->careTeamInvitationDefinition()->translationKey;

        return ValidationException::withMessages([
            'invitation' => [trans("{$translationKey}.accept.invalid")],
        ]);
    }

    /** @param DoctorInvitation|FamilyInvitation $invitation */
    private function acceptLockedInvitation(User $user, Model $invitation): void
    {
        $definition = $this->careTeamInvitationDefinition();

        $this->linkInviteeProfileFromInvitation($user, $invitation);

        $invitation->loadMissing('patient.user');

        $patientEmail = $invitation->patient->user->email;

        if ($patientEmail !== null && $patientEmail !== '') {
            Mail::to($patientEmail)->queue(new $definition->acceptedMailableClass(
                accepterName: (string) $user->name,
            ));
        }

        $this->securityActivityLogger()->record(
            $definition->acceptedActivity,
            causer: $user,
            subject: $invitation,
            properties: [
                'patient_id' => $invitation->patient_id,
            ],
        );
    }

    private function ensureUserMayAcceptInvitation(User $user): void
    {
        if (! $this->userMayAcceptInvitation($user)) {
            throw $this->invalidInvitationException();
        }

        if ($user->email_verified_at === null) {
            throw $this->invalidInvitationException();
        }
    }

    /** @param DoctorInvitation|FamilyInvitation $invitation */
    private function invitationMatchesUserEmail(User $user, Model $invitation): bool
    {
        foreach (User::emailHashCandidates($user->email) as $candidate) {
            if (hash_equals($invitation->invited_email_hash, $candidate)) {
                return true;
            }
        }

        return false;
    }

    private function patientHasPendingInvitationForEmail(Patient $patient, string $emailHash, bool $lockForUpdate = false): bool
    {
        $invitationModelClass = $this->careTeamInvitationDefinition()->invitationModelClass;

        $query = $invitationModelClass::query()
            ->where('patient_id', $patient->id)
            ->where('invited_email_hash', $emailHash)
            ->pending();

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->exists();
    }
}
