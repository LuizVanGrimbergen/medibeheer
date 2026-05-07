<?php

namespace App\Providers;

use App\Models\AppointmentTransportInvitation;
use App\Models\FamilyInvitation;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::bind('transportInvitation', function (string $value): AppointmentTransportInvitation {
            return self::bindFamilyTransportInvitation($value);
        });

        Route::bind('familyInvitation', function (string $value): FamilyInvitation {
            return self::bindPatientFamilyInvitation($value);
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

    private static function bindPatientFamilyInvitation(string $value): FamilyInvitation
    {
        $user = request()->user();
        abort_unless($user !== null && $user->isPatient(), 404);

        $patient = $user->patient;
        abort_unless($patient !== null, 404);

        return FamilyInvitation::query()
            ->whereKey($value)
            ->where('patient_id', $patient->id)
            ->firstOrFail();
    }
}
