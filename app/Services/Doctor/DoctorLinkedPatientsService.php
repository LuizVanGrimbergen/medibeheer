<?php

declare(strict_types=1);

namespace App\Services\Doctor;

use App\Models\Doctor;
use App\Models\Patient;

final class DoctorLinkedPatientsService
{
    /** @return list<array{public_id: string, name: string}> */
    public function listForDoctor(Doctor $doctor): array
    {
        return $doctor->patients()
            ->with('user')
            ->orderBy('patients.id')
            ->get()
            ->map(static function (Patient $patient): array {
                $user = $patient->user;

                return [
                    'public_id' => $user->public_id,
                    'name' => $user->name,
                    'unlink_url' => route('doctor.patients.links.destroy', ['linkedPatient' => $user->public_id], absolute: false),
                ];
            })
            ->values()
            ->all();
    }
}
