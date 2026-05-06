<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;

final class FamilyDashboardState
{
    public static function inertiaPayload(Request $request): array
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isFamilyMember()) {
            return self::emptyPayload();
        }

        $family = $user->family;

        if ($family === null) {
            return self::emptyPayload();
        }

        $patients = $family
            ->patients()
            ->with('user')
            ->orderBy('id')
            ->get();

        $activePatientId = $request->session()->get('family.active_patient_id');

        if (is_string($activePatientId) && is_numeric($activePatientId)) {
            $activePatientId = (int) $activePatientId;
        }

        if (! is_int($activePatientId)) {
            $activePatientId = null;
        }

        if ($activePatientId !== null && $patients->where('id', $activePatientId)->isEmpty()) {
            $activePatientId = null;
        }

        if ($activePatientId === null) {
            $firstPatient = $patients->first();

            if ($firstPatient !== null) {
                $activePatientId = (int) $firstPatient->id;
                $request->session()->put('family.active_patient_id', $activePatientId);
            }
        }

        return [
            'has_linked_patient' => $patients->isNotEmpty(),
            'active_patient_id' => $activePatientId,
            'patients' => $patients
                ->map(function (Patient $patient) use ($activePatientId): array {
                    $name = $patient->user?->name ?? 'Patient';

                    return [
                        'id' => (int) $patient->id,
                        'name' => (string) $name,
                        'switch_url' => route('family.patients.switch', $patient, absolute: false),
                        'is_active' => $activePatientId !== null && (int) $patient->id === (int) $activePatientId,
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    private static function emptyPayload(): array
    {
        return [
            'has_linked_patient' => false,
            'active_patient_id' => null,
            'patients' => [],
        ];
    }
}
