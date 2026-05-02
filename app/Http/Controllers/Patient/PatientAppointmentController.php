<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientAppointmentController extends Controller
{
    use AuthorizesPatientProfile;

    public function __invoke(Request $request): Response
    {
        $this->authorizePatientProfile($request);

        return Inertia::render('Patient/Appointments');
    }
}
