<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\DailyCheckin;
use App\Models\Family;
use App\Models\Patient;
use App\Models\User;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;

final class FamilyDashboardState
{
    public static function activePatientId(Request $request): ?int
    {
        $context = self::familyContext($request);

        if ($context === null) {
            return null;
        }

        return $context['activePatientId'];
    }

    public static function activePatient(Request $request): ?Patient
    {
        $context = self::familyContext($request);

        if ($context === null) {
            return null;
        }

        if (! $context['activePatient'] instanceof Patient) {
            return null;
        }

        return $context['activePatient'];
    }

    public static function inertiaPayload(Request $request): array
    {
        $context = self::familyContext($request);

        if ($context === null) {
            return self::emptyPayload();
        }

        /** @var EloquentCollection<int, Patient> $patients */
        $patients = $context['patients'];
        $activePatientId = $context['activePatientId'];

        return [
            'has_linked_patient' => $patients->isNotEmpty(),
            'active_patient_id' => $activePatientId,
            'active_patient_today_mood' => $context['activePatientTodayMood'],
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

    private static function resolveFamily(Request $request): ?Family
    {
        return self::requestCached(
            $request,
            'family.dashboard.family',
            function () use ($request): ?Family {
                $user = $request->user();

                if (! $user instanceof User || ! $user->isFamilyMember()) {
                    return null;
                }

                return $user->family;
            },
        );
    }

    private static function familyContext(Request $request): ?array
    {
        return self::requestCached(
            $request,
            'family.dashboard.context',
            function () use ($request): ?array {
                $family = self::resolveFamily($request);

                if (! $family instanceof Family) {
                    return null;
                }

                $patients = $family
                    ->patients()
                    ->with('user')
                    ->orderBy('patients.id')
                    ->get();

                $patientIds = $patients->pluck('id')->map(fn ($id): int => (int) $id)->all();
                $activePatientId = self::syncedActivePatientIdFromIds($request, $patientIds);
                $activePatient = $activePatientId === null
                    ? null
                    : $patients->firstWhere('id', $activePatientId);
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
                    'patients' => $patients,
                    'activePatientId' => $activePatientId,
                    'activePatient' => $activePatient,
                    'activePatientTodayMood' => $activePatientTodayMood,
                ];
            },
        );
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

    private static function requestCached(Request $request, string $key, Closure $resolver): mixed
    {
        if ($request->attributes->has($key)) {
            return $request->attributes->get($key);
        }

        $resolved = $resolver();
        $request->attributes->set($key, $resolved);

        return $resolved;
    }
}
