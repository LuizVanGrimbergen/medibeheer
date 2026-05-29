<?php

namespace App\Http\Controllers\Doctor\Concerns;

use App\Models\Doctor;
use Illuminate\Http\Request;

trait AuthorizesDoctorProfile
{
    protected function authorizeDoctorProfile(Request $request): Doctor
    {
        $user = $request->user();

        abort_unless($user !== null && $user->isDoctor(), 403);

        $doctor = $user->doctor;

        if ($doctor === null) {
            $doctor = Doctor::query()->firstOrCreate(
                ['user_id' => $user->id],
            );
        }

        $this->authorize('view', $doctor);

        return $doctor;
    }
}
