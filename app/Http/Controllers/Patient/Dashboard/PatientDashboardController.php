<?php

namespace App\Http\Controllers\Patient\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Models\Medication;
use App\Services\Medications\PatientScheduledIntakesQuery;
use App\Support\Medications\MedicationIntakeClock;
use App\Support\Medications\PatientRecentPushMedicationMarkStore;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientDashboardController extends Controller
{
    use AuthorizesPatientProfile;

    public function __construct(
        private readonly PatientScheduledIntakesQuery $scheduledIntakesQuery,
        private readonly PatientRecentPushMedicationMarkStore $recentPushMarkStore,
    ) {}

    public function __invoke(Request $request): Response
    {
        $patient = $this->authorizePatientProfile($request);
        $user = $request->user();

        $today = MedicationIntakeClock::today();

        return Inertia::render('Patient/Dashboard', [
            'today_date' => $today->toDateString(),
            'today_checkin' => Inertia::defer(fn () => $patient
                ->dailyCheckins()
                ->with('selectedSymptoms')
                ->whereDate('checkin_date', $today->toDateString())
                ->first()
                ?->toDashboardPayload()),
            'today_medication_intakes' => Inertia::defer(
                fn () => $this->scheduledIntakesQuery->forPatientOnDate($patient, $today),
            ),
            'pending_push_medication_mark' => $this->recentPushMarkStore->peek($patient->id),
            'has_medications' => $patient->medications()->exists(),
            'can_create_medication' => $user->can('create', Medication::class),
        ]);
    }
}
