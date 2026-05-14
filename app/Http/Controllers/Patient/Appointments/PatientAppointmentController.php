<?php

namespace App\Http\Controllers\Patient\Appointments;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Http\Requests\Patient\Appointments\StoreAppointmentRequest;
use App\Http\Requests\Patient\Appointments\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Services\AppointmentTransportInvitationService;
use App\Services\PatientAppointmentsScreenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientAppointmentController extends Controller
{
    use AuthorizesPatientProfile;

    public function __construct(
        private readonly AppointmentTransportInvitationService $appointmentTransportInvitationService,
        private readonly PatientAppointmentsScreenService $patientAppointmentsScreenService,
    ) {}

    public function index(Request $request): Response
    {
        $patient = $this->authorizePatientProfile($request);

        return Inertia::render(
            'Patient/Appointments',
            [
                ...$this->patientAppointmentsScreenService->buildProps($patient),
                'open_create_dialog' => $request->boolean('open_create'),
            ],
        );
    }

    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $patient = $this->authorizePatientProfile($request);

        $this->authorize('create', Appointment::class);

        $validated = $request->validated();
        $requestedFamilyIds = $validated['transport_family_ids'] ?? null;
        unset($validated['transport_family_ids']);

        $appointment = $patient->appointments()->create($validated);

        $this->appointmentTransportInvitationService->syncForAppointment(
            $appointment,
            is_array($requestedFamilyIds) ? $requestedFamilyIds : null,
        );

        return redirect()->route('patient.appointments');
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizePatientProfile($request);

        $this->authorize('update', $appointment);

        if ($appointment->status === AppointmentStatus::CANCELLED) {
            return redirect()->route('patient.appointments');
        }

        $validated = $request->validated();
        $requestedFamilyIds = $validated['transport_family_ids'] ?? null;
        unset($validated['transport_family_ids']);

        $appointment->update($validated);

        $this->appointmentTransportInvitationService->syncForAppointment(
            $appointment,
            is_array($requestedFamilyIds) ? $requestedFamilyIds : null,
        );

        $followUp = $request->query('outcome_follow_up');
        $status = $validated['status'] ?? null;
        $statusEnum = match (true) {
            $status instanceof AppointmentStatus => $status,
            is_string($status) => AppointmentStatus::tryFrom($status),
            default => null,
        };

        if (
            is_string($followUp)
            && $statusEnum !== null
            && (
                ($followUp === 'done' && $statusEnum === AppointmentStatus::DONE)
                || ($followUp === 'cancelled' && $statusEnum === AppointmentStatus::CANCELLED)
            )
        ) {
            return redirect()->route('patient.appointments.schedule-next', [
                'outcome' => $followUp,
            ]);
        }

        return redirect()->route('patient.appointments');
    }

    public function destroy(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizePatientProfile($request);

        $this->authorize('delete', $appointment);

        Appointment::destroy($appointment->getKey());

        return redirect()->route('patient.appointments');
    }
}
