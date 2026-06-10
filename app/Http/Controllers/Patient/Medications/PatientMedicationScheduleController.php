<?php

namespace App\Http\Controllers\Patient\Medications;

use App\Enums\MedicationIntakeFrequency;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Http\Requests\Patient\Medications\StoreMedicationScheduleRequest;
use App\Http\Requests\Patient\Medications\UpdateMedicationScheduleRequest;
use App\Models\Medication;
use App\Models\MedicationSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PatientMedicationScheduleController extends Controller
{
    use AuthorizesPatientProfile;

    public function store(StoreMedicationScheduleRequest $request, Medication $medication): RedirectResponse
    {
        $this->authorizePatientProfile($request);

        $validated = $request->validated();
        $weekdays = Arr::pull($validated, 'intake_weekdays');

        $schedule = $medication->schedules()->create($validated);

        $schedule->syncIntakeWeekdays(is_array($weekdays) ? $weekdays : null);

        return redirect()->route('patient.medications');
    }

    public function update(
        UpdateMedicationScheduleRequest $request,
        Medication $medication,
        MedicationSchedule $schedule,
    ): RedirectResponse {
        $this->authorizePatientProfile($request);

        if (! $schedule->medication->is($medication)) {
            abort(404);
        }

        $validated = $request->validated();
        $weekdays = Arr::pull($validated, 'intake_weekdays', '__missing__');

        $schedule->fill($validated)->save();
        $schedule->refresh();

        if ($weekdays !== '__missing__') {
            if ($schedule->intake_frequency === MedicationIntakeFrequency::WEEKDAYS) {
                $schedule->syncIntakeWeekdays(is_array($weekdays) ? $weekdays : null);
            } else {
                $schedule->syncIntakeWeekdays(null);
            }
        } elseif (
            array_key_exists('intake_frequency', $validated)
            && $validated['intake_frequency'] !== MedicationIntakeFrequency::WEEKDAYS
        ) {
            $schedule->syncIntakeWeekdays(null);
        }

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
