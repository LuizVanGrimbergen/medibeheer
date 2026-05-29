<?php

namespace App\Http\Resources\Patient;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Doctor */
class LinkedDoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->user->public_id,
            'name' => (string) $this->user->name,
            'unlink_url' => route('patient.doctors.links.destroy', ['linkedDoctor' => $this->user->public_id], absolute: false),
        ];
    }
}
