<?php

namespace App\Services;

use App\Mail\FamilyInvitationMail;
use App\Models\Family;
use App\Models\FamilyInvitation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

final class FamilyInvitationService
{
    public static function normalizeInviteCode(string $code): string
    {
        return strtolower(preg_replace('/\s+/', '', trim($code)));
    }

    public function create(Patient $patient, string $email): void
    {
        $normalized = User::normalizeEmail($email);

        $emailHash = User::hashEmail($normalized);

        $plainToken = bin2hex(random_bytes(20));
        $tokenHash = hash('sha256', $plainToken);
        $expiresAt = now()->addDays((int) config('services.family_invitation.expiry_days', 14));

        $invitation = DB::transaction(function () use ($patient, $normalized, $emailHash, $tokenHash, $expiresAt): FamilyInvitation {
            Patient::query()->whereKey($patient->id)->lockForUpdate()->firstOrFail();

            if ($this->patientHasPendingInvitationForEmail($patient, $emailHash, lockForUpdate: true)) {
                throw ValidationException::withMessages([
                    'email' => [trans('family_invitation.validation.duplicate_pending')],
                ]);
            }

            return FamilyInvitation::create([
                'patient_id' => $patient->id,
                'invited_email' => $normalized,
                'invited_email_hash' => $emailHash,
                'token_hash' => $tokenHash,
                'expires_at' => $expiresAt,
            ]);
        });

        try {
            Mail::to($normalized)->send(new FamilyInvitationMail(
                plainToken: $plainToken,
                expiresAt: $expiresAt,
            ));
        } catch (\Throwable $e) {
            report($e);

            $invitation->delete();

            throw ValidationException::withMessages([
                'email' => [trans('family_invitation.flash.mail_failed')],
            ]);
        }
    }

    public function revoke(FamilyInvitation $invitation): void
    {
        if ($invitation->accepted_at !== null || $invitation->revoked_at !== null) {
            return;
        }

        $invitation->forceFill(['revoked_at' => now()])->save();
    }

    public function accept(User $user, string $plainCode): void
    {
        $tokenHash = $this->validatedAcceptTokenHash($user, $plainCode);

        DB::transaction(function () use ($user, $tokenHash): void {
            $invitation = $this->findLockablePendingInvitation($tokenHash);

            if (! $this->invitationMatchesUserEmail($user, $invitation)) {
                throw $this->invalidCodeException();
            }

            $this->linkFamilyProfileFromInvitation($user, $invitation);
        });
    }

    private function invalidCodeException(): ValidationException
    {
        return ValidationException::withMessages([
            'code' => [trans('family_invitation.accept.invalid')],
        ]);
    }

    private function validatedAcceptTokenHash(User $user, string $plainCode): string
    {
        if (! $user->isFamilyMember()) {
            throw $this->invalidCodeException();
        }

        if ($user->email_verified_at === null) {
            throw $this->invalidCodeException();
        }

        $normalizedCode = self::normalizeInviteCode($plainCode);

        if (strlen($normalizedCode) !== 40 || ! ctype_xdigit($normalizedCode)) {
            throw $this->invalidCodeException();
        }

        return hash('sha256', $normalizedCode);
    }

    private function findLockablePendingInvitation(string $tokenHash): FamilyInvitation
    {
        $invitation = FamilyInvitation::query()
            ->where('token_hash', $tokenHash)
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->lockForUpdate()
            ->first();

        if ($invitation === null) {
            throw $this->invalidCodeException();
        }

        return $invitation;
    }

    private function invitationMatchesUserEmail(User $user, FamilyInvitation $invitation): bool
    {
        foreach (User::emailHashCandidates($user->email) as $candidate) {
            if (hash_equals($invitation->invited_email_hash, $candidate)) {
                return true;
            }
        }

        return false;
    }

    private function linkFamilyProfileFromInvitation(User $user, FamilyInvitation $invitation): void
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

    private function patientHasPendingInvitationForEmail(Patient $patient, string $emailHash, bool $lockForUpdate = false): bool
    {
        $query = FamilyInvitation::query()
            ->where('patient_id', $patient->id)
            ->where('invited_email_hash', $emailHash)
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now());

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->exists();
    }
}
