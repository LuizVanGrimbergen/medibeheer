<?php

namespace App\Http\Controllers\Patient\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Services\Medications\PatientScheduledIntakesQuery;
use App\Support\Medications\MedicationIntakeClock;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientDashboardController extends Controller
{
    use AuthorizesPatientProfile;

    public function __construct(
        private readonly PatientScheduledIntakesQuery $scheduledIntakesQuery,
    ) {}

    public function __invoke(Request $request): Response
    {
        $patient = $this->authorizePatientProfile($request);

        $today = MedicationIntakeClock::today();

        $todayCheckin = $patient
            ->dailyCheckins()
            ->with('selectedSymptoms')
            ->whereDate('checkin_date', $today->toDateString())
            ->first();

        return Inertia::render('Patient/Dashboard', [
            'today_date' => $today->toDateString(),
            'today_checkin' => $todayCheckin?->toDashboardPayload(),
            'today_medication_intakes' => $this->scheduledIntakesQuery->forPatientOnDate($patient, $today),
        ]);
    }
}
