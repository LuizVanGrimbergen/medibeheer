<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patient\Appointments;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Http\Resources\Appointments\PatientAppointmentResource;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ShowPatientAppointmentCompletePageController extends Controller
{
    use AuthorizesPatientProfile;

    public function __invoke(Request $request, Appointment $appointment): Response|RedirectResponse
    {
        $this->authorizePatientProfile($request);

        $this->authorize('update', $appointment);

        $followUpOutcome = $request->session()->pull('appointment_follow_up_outcome');
        $showScheduleNextPrompt = $request->boolean('schedule_next')
            || $followUpOutcome === 'done';

        if ($appointment->status === AppointmentStatus::DONE) {
            if ($showScheduleNextPrompt) {
                return $this->renderOutcomePage($appointment, showScheduleNextPrompt: true);
            }

            return redirect()->route('patient.appointments');
        }

        if ($appointment->status !== AppointmentStatus::SCHEDULED) {
            return redirect()->route('patient.appointments');
        }

        return $this->renderOutcomePage($appointment);
    }

    private function renderOutcomePage(
        Appointment $appointment,
        bool $showScheduleNextPrompt = false,
    ): Response {
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

        return Inertia::render('Patient/Appointments/Complete', [
            'appointment' => (new PatientAppointmentResource($appointment))->resolve(),
            'show_schedule_next_prompt' => $showScheduleNextPrompt,
        ]);
    }
}
