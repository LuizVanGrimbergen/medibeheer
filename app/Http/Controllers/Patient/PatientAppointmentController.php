<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Http\Requests\Patient\StoreAppointmentRequest;
use App\Http\Requests\Patient\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientAppointmentController extends Controller
{
    use AuthorizesPatientProfile;

    public function index(Request $request): Response
    {
        $patient = $this->authorizePatientProfile($request);

        $appointments = $patient->appointments()
            ->orderBy('starts_at')
            ->get();

        return Inertia::render('Patient/Appointments', [
            'appointments' => AppointmentResource::collection($appointments)->resolve(),
        ]);
    }

    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $patient = $this->authorizePatientProfile($request);

        $this->authorize('create', Appointment::class);

        $patient->appointments()->create($request->validated());

        return redirect()->route('patient.appointments');
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizePatientProfile($request);

        $this->authorize('update', $appointment);

        $appointment->update($request->validated());

        return redirect()->route('patient.appointments');
    }

    public function destroy(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizePatientProfile($request);

        $this->authorize('delete', $appointment);

        $appointment->delete();

        return redirect()->route('patient.appointments');
    }
}
