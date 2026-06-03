<?php

namespace App\Http\Controllers\Patient\Concerns;

use App\Models\Patient;
use Illuminate\Http\Request;

trait AuthorizesPatientProfile
{
    protected function authorizePatientProfile(Request $request): Patient
    {
        $user = $request->user();

        abort_unless($user !== null && $user->isPatient(), 403);

        $patient = $user->patient;

        if ($patient === null) {
            $patient = Patient::query()->firstOrCreate(
                ['user_id' => $user->id],
            );
        }

        $this->authorize('view', $patient);

        return $patient;
    }
}
