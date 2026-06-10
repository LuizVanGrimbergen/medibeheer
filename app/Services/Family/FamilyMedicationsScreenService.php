<?php

declare(strict_types=1);

namespace App\Services\Family;

use App\Models\Medication;
use App\Models\Patient;
use App\Services\Medications\PatientScheduledIntakesQuery;
use App\Services\Patient\PatientMedicationRegisterService;
use App\Support\Family\FamilyMedicationIntakeCalendarSlotPayload;
use App\Support\InertiaPagination;
use Illuminate\Http\Request;

final class FamilyMedicationsScreenService
{
    public function __construct(
        private readonly PatientMedicationRegisterService $patientMedicationRegisterService,
        private readonly PatientScheduledIntakesQuery $scheduledIntakesQuery,
    ) {}

    /** @return array{medications: array{data: list<array<string, mixed>>, meta: array<string, mixed>}} */
    public function paginatedMedicationsFor(Request $request, Patient $patient): array
    {
        $deepLinkMedicationId = $this->resolveDeepLinkMedicationId($request, $patient);
        $page = $this->resolvePage($request, $patient, $deepLinkMedicationId);

        return $this->patientMedicationRegisterService->buildFamilyMedicationsProps($patient, $page);
    }

    /** @return array{medication_calendar_days: list<array<string, mixed>>, medication_calendar_slots: list<array<string, mixed>>} */
    public function calendarDataFor(Patient $patient, string $calendarMonth): array
    {
        $calendar = $this->scheduledIntakesQuery->monthCalendarDataForPatient($patient, $calendarMonth);

        return [
            'medication_calendar_days' => $calendar['days'],
            'medication_calendar_slots' => FamilyMedicationIntakeCalendarSlotPayload::collect($calendar['slots']),
        ];
    }

    private function resolveDeepLinkMedicationId(Request $request, Patient $patient): ?int
    {
        $raw = $request->query('medication');

        if (! is_string($raw) || ! ctype_digit($raw)) {
            return null;
        }

        $medicationId = (int) $raw;

        $exists = Medication::query()
            ->whereKey($medicationId)
            ->where('patient_id', $patient->id)
            ->exists();

        if (! $exists) {
            return null;
        }

        return $medicationId;
    }

    private function resolvePage(Request $request, Patient $patient, ?int $deepLinkMedicationId): int
    {
        if ($deepLinkMedicationId === null) {
            return $this->normalizedPage($request);
        }

        $earlierCount = $patient->medications()
            ->withTrashed()
            ->where('id', '>', $deepLinkMedicationId)
            ->count();

        return intdiv($earlierCount, InertiaPagination::PER_PAGE) + 1;
    }

    private function normalizedPage(Request $request): int
    {
        $raw = $request->query('page', 1);

        if (is_string($raw) && ctype_digit($raw)) {
            $page = (int) $raw;

            if ($page >= 1) {
                return $page;
            }
        }

        if (is_int($raw) && $raw >= 1) {
            return $raw;
        }

        return 1;
    }
}
