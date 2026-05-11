<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patient\Appointments;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ShowPatientAppointmentScheduleNextPageController extends Controller
{
    use AuthorizesPatientProfile;

    public function __invoke(Request $request): Response|RedirectResponse
    {
        $this->authorizePatientProfile($request);

        $outcome = $request->query('outcome');

        if ($outcome !== 'done' && $outcome !== 'cancelled') {
            return redirect()->route('patient.appointments');
        }

        return Inertia::render('Patient/Appointments/ScheduleNext', [
            'outcome' => $outcome,
        ]);
    }
}
