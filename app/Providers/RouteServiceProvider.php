<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\AppointmentTransportInvitation;
use App\Models\Doctor;
use App\Models\DoctorInvitation;
use App\Models\FamilyInvitation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::bind('transportInvitation', function (string $value): AppointmentTransportInvitation {
            return self::bindFamilyTransportInvitation($value);
        });

        Route::bind('familyInvitation', fn (string $value): FamilyInvitation => self::bindPatientInvitation(
            $value,
            FamilyInvitation::class,
        ));

        Route::bind('incomingFamilyInvitation', fn (string $value): FamilyInvitation => self::bindIncomingInvitation(
            $value,
            FamilyInvitation::class,
            static fn (User $user): bool => $user->isFamilyMember(),
        ));

        Route::bind('doctorInvitation', fn (string $value): DoctorInvitation => self::bindPatientInvitation(
            $value,
            DoctorInvitation::class,
        ));

        Route::bind('incomingDoctorInvitation', fn (string $value): DoctorInvitation => self::bindIncomingInvitation(
            $value,
            DoctorInvitation::class,
            static fn (User $user): bool => $user->isDoctor(),
        ));

        Route::bind('linkedFamilyMember', function (string $value): User {
            return self::bindPatientLinkedFamilyMember($value);
        });

        Route::bind('linkedDoctor', function (string $value): Doctor {
            return self::bindPatientLinkedDoctor($value);
        });

        Route::bind('linkedPatient', function (string $value): Patient {
            return self::bindDoctorLinkedPatient($value);
        });
    }

    private static function bindFamilyTransportInvitation(string $value): AppointmentTransportInvitation
    {
        $user = request()->user();
        abort_unless($user !== null && $user->isFamilyMember(), 404);

        $family = $user->familyOrCreate();

        return AppointmentTransportInvitation::query()
            ->whereKey($value)
            ->where('family_id', $family->id)
            ->firstOrFail();
    }

    private static function bindPatientInvitation(string $value, string $modelClass): Model
    {
        $user = request()->user();
        abort_unless($user !== null && $user->isPatient(), 404);

        $patient = $user->patient;
        abort_unless($patient !== null, 404);

        return $modelClass::query()
            ->where('public_id', $value)
            ->where('patient_id', $patient->id)
            ->firstOrFail();
    }

    private static function bindIncomingInvitation(string $value, string $modelClass, callable $roleCheck): Model
    {
        $user = request()->user();
        abort_unless($user !== null && $roleCheck($user), 404);

        $emailHashes = self::verifiedUserEmailHashes($user);

        if ($emailHashes === []) {
            abort(404);
        }

        return $modelClass::query()
            ->where('public_id', $value)
            ->whereIn('invited_email_hash', $emailHashes, 'and', false)
            ->pending()
            ->firstOrFail();
    }

    private static function verifiedUserEmailHashes(User $user): array
    {
        if ($user->email_verified_at === null) {
            return [];
        }

        if ($user->email === null || $user->email === '') {
            return [];
        }

        return User::emailHashCandidates($user->email);
    }

    private static function bindPatientLinkedFamilyMember(string $value): User
    {
        $user = request()->user();
        abort_unless($user !== null && $user->isPatient(), 404);

        $patient = $user->patient;
        abort_unless($patient !== null, 404);

        $familyMemberUser = User::query()
            ->where('public_id', $value)
            ->where('role', UserRole::FAMILY_MEMBER)
            ->firstOrFail();

        $family = $familyMemberUser->family;
        abort_unless($family !== null, 404);
        abort_unless($family->patients()->whereKey($patient->id)->exists(), 404);

        return $familyMemberUser;
    }

    private static function bindPatientLinkedDoctor(string $value): Doctor
    {
        $user = request()->user();
        abort_unless($user !== null && $user->isPatient(), 404);

        $patient = $user->patient;
        abort_unless($patient !== null, 404);

        $doctor = Doctor::query()
            ->whereHas('user', static fn ($query) => $query->where('public_id', $value))
            ->firstOrFail();

        abort_unless($patient->doctors()->whereKey($doctor->id)->exists(), 404);

        return $doctor;
    }

    private static function bindDoctorLinkedPatient(string $value): Patient
    {
        $user = request()->user();
        abort_unless($user !== null && $user->isDoctor(), 404);

        $doctor = $user->doctor;
        abort_unless($doctor !== null, 404);

        $patient = Patient::query()
            ->whereHas('user', static fn ($query) => $query->where('public_id', $value))
            ->firstOrFail();

        abort_unless($doctor->patients()->whereKey($patient->id)->exists(), 404);

        return $patient;
    }
}
