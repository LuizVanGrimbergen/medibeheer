<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\DailyCheckin;
use App\Models\Patient;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

final class FamilyDashboardState
{
    public static function activePatientId(Request $request): ?int
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isFamilyMember()) {
            return null;
        }

        $family = $user->family;

        if ($family === null) {
            return null;
        }

        $patientIds = $family->patients()
            ->orderBy('patients.id')
            ->pluck('patients.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($patientIds === []) {
            return null;
        }

        return self::syncedActivePatientIdFromIds($request, $patientIds);
    }

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
            ->orderBy('patients.id')
            ->get();

        $activePatientId = self::syncedActivePatientIdFromIds(
            $request,
            $patients->pluck('id')->map(fn ($id) => (int) $id)->all(),
        );

        $activePatientTodayMood = null;

        if ($activePatientId !== null) {
            $today = CarbonImmutable::today()->toDateString();

            $todayCheckin = DailyCheckin::query()
                ->where('patient_id', $activePatientId)
                ->whereDate('checkin_date', '=', $today, 'and')
                ->first();

            $activePatientTodayMood = $todayCheckin?->mood_score?->value;
        }

        return [
            'has_linked_patient' => $patients->isNotEmpty(),
            'active_patient_id' => $activePatientId,
            'active_patient_today_mood' => $activePatientTodayMood,
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

    private static function syncedActivePatientIdFromIds(Request $request, array $patientIds): ?int
    {
        if ($patientIds === []) {
            return null;
        }

        $activePatientId = $request->session()->get('family.active_patient_id');

        if (is_string($activePatientId) && is_numeric($activePatientId)) {
            $activePatientId = (int) $activePatientId;
        }

        if (! is_int($activePatientId)) {
            $activePatientId = null;
        }

        if ($activePatientId !== null && ! in_array($activePatientId, $patientIds, true)) {
            $activePatientId = null;
        }

        if ($activePatientId === null) {
            $activePatientId = $patientIds[0];
            $request->session()->put('family.active_patient_id', $activePatientId);
        }

        return $activePatientId;
    }

    private static function emptyPayload(): array
    {
        return [
            'has_linked_patient' => false,
            'active_patient_id' => null,
            'active_patient_today_mood' => null,
            'patients' => [],
        ];
    }
}
