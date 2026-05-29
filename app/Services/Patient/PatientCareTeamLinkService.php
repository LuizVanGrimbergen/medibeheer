<?php

declare(strict_types=1);

namespace App\Services\Patient;

use App\Enums\SecurityActivityDescription;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Services\Audit\SecurityActivityLogger;

final class PatientCareTeamLinkService
{
    public function __construct(
        private readonly SecurityActivityLogger $securityActivityLogger,
    ) {}

    public function unlinkFamilyMember(Patient $patient, User $familyMemberUser, User $removedBy): void
    {
        $family = $familyMemberUser->family;

        if ($family === null || ! $family->patients()->whereKey($patient->id)->exists()) {
            abort(404);
        }

        $family->patients()->detach($patient->id);

        $this->securityActivityLogger->record(
            SecurityActivityDescription::FAMILY_MEMBER_UNLINKED,
            causer: $removedBy,
            subject: $family,
            properties: [
                'patient_id' => $patient->id,
                'family_member_user_id' => $familyMemberUser->id,
            ],
        );
    }

    public function unlinkDoctor(Patient $patient, Doctor $doctor, User $removedBy): void
    {
        if (! $patient->doctors()->whereKey($doctor->id)->exists()) {
            abort(404);
        }

        $patient->doctors()->detach($doctor->id);

        $this->securityActivityLogger->record(
            SecurityActivityDescription::DOCTOR_UNLINKED,
            causer: $removedBy,
            subject: $doctor,
            properties: [
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
            ],
        );
    }

    public function unlinkPatient(Doctor $doctor, Patient $patient, User $removedBy): void
    {
        if (! $doctor->patients()->whereKey($patient->id)->exists()) {
            abort(404);
        }

        $doctor->patients()->detach($patient->id);

        $this->securityActivityLogger->record(
            SecurityActivityDescription::DOCTOR_UNLINKED,
            causer: $removedBy,
            subject: $patient,
            properties: [
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
            ],
        );
    }
}
