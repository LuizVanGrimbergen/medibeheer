<?php

namespace App\Http\Requests\Patient;

use App\Enums\AppointmentStatus;
use App\Enums\DoctorType;
use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    /**************************************/
    /*           Authorization */
    /**************************************/

    public function authorize(): bool
    {
        return $this->user()?->can('create', Appointment::class) ?? false;
    }

    /**************************************/
    /*          Validation Rules */
    /**************************************/

    public function rules(): array
    {
        return [
            'doctor_type' => ['required', Rule::enum(DoctorType::class)],
            'provider_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:2000'],
            'starts_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'status' => ['sometimes', Rule::enum(AppointmentStatus::class)],
        ];
    }
}
