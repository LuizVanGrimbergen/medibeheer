<?php

use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('patient.{patientId}.family-updates', function (User $user, int $patientId): bool {
    $patient = Patient::query()->find($patientId);

    if ($patient === null) {
        return false;
    }

    return $user->can('view', $patient);
});
