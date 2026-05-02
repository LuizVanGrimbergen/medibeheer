<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Doctor\Concerns\AuthorizesDoctorProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DoctorDashboardController extends Controller
{
    use AuthorizesDoctorProfile;

    public function __invoke(Request $request): Response
    {
        $this->authorizeDoctorProfile($request);

        return Inertia::render('Doctor/Dashboard');
    }
}
