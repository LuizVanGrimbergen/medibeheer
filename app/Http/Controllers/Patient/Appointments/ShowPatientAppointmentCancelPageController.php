<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patient\Appointments;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Http\Resources\PatientAppointmentResource;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ShowPatientAppointmentCancelPageController extends Controller
{
    use AuthorizesPatientProfile;

    public function __invoke(Request $request, Appointment $appointment): Response|RedirectResponse
    {
        $this->authorizePatientProfile($request);

        $this->authorize('update', $appointment);

        if ($appointment->status !== AppointmentStatus::SCHEDULED) {
            return redirect()->route('patient.appointments');
        }

        $appointment->load([
            'transportFamily.user',
            'transportInvitations' => static fn ($query) => $query->select(
                'id',
                'appointment_id',
                'family_id',
                'invited_at',
                'accepted_at',
                'declined_at',
                'cancelled_at',
            ),
        ]);
        $appointment->loadExists([
            'transportInvitations as has_pending_transport_invitation' => static fn ($query) => $query->pending(),
        ]);

        return Inertia::render('Patient/Appointments/Cancel', [
            'appointment' => (new PatientAppointmentResource($appointment))->resolve(),
        ]);
    }
}
