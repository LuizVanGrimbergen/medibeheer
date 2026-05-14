<?php

namespace App\Http\Controllers\Patient\Medications;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Http\Requests\Patient\Medications\StoreMedicationScheduleRequest;
use App\Http\Requests\Patient\Medications\UpdateMedicationScheduleRequest;
use App\Models\Medication;
use App\Models\MedicationSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PatientMedicationScheduleController extends Controller
{
    use AuthorizesPatientProfile;

    public function store(StoreMedicationScheduleRequest $request, Medication $medication): RedirectResponse
    {
        $this->authorizePatientProfile($request);

        $this->authorize('update', $medication);

        $medication->schedules()->create(array_merge($request->validated(), [
            'patient_id' => $medication->patient_id,
            'family_id' => $medication->family_id,
        ]));

        return redirect()->route('patient.medications');
    }

    public function update(
        UpdateMedicationScheduleRequest $request,
        Medication $medication,
        MedicationSchedule $schedule,
    ): RedirectResponse {
        $this->authorizePatientProfile($request);

        $this->authorize('update', $medication);

        $schedule->update($request->validated());

        return redirect()->route('patient.medications');
    }

    public function destroy(Request $request, Medication $medication, MedicationSchedule $schedule): RedirectResponse
    {
        $this->authorizePatientProfile($request);

        $this->authorize('update', $medication);

        if (! $schedule->medication->is($medication)) {
            abort(404);
        }

        MedicationSchedule::destroy($schedule->getKey());

        return redirect()->route('patient.medications');
    }
}
