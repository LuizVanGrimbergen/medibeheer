<?php

namespace App\Http\Requests\Patient;

use App\Enums\AppointmentStatus;
use App\Enums\DoctorType;
use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentRequest extends FormRequest
{

    /**************************************/
    /*           Authorization */
    /**************************************/

    public function authorize(): bool
    {
        $appointment = $this->route('appointment');

        if (! $appointment instanceof Appointment) {
            return false;
        }

        return $this->user()?->can('update', $appointment) ?? false;
    }

    /**************************************/
    /*          Validation Rules */
    /**************************************/
    public function rules(): array
    {
        /** @var Appointment $appointment */
        $appointment = $this->route('appointment');

        return [
            'doctor_type' => ['sometimes', 'required', Rule::enum(DoctorType::class)],
            'provider_name' => ['sometimes', 'required', 'string', 'max:255'],
            'address' => ['sometimes', 'required', 'string', 'max:2000'],
            'starts_at' => ['sometimes', 'required', 'date'],
            'needs_transport' => ['sometimes', 'required', 'boolean'],
            'transport_family_ids' => Rule::when(
                $this->has('needs_transport') && $this->boolean('needs_transport'),
                ['required', 'array', 'min:1'],
                ['sometimes', 'array'],
            ),
            'transport_family_ids.*' => [
                'integer',
                Rule::exists('family_patient', 'family_id')->where(
                    'patient_id',
                    $appointment->patient_id,
                ),
            ],
            'notes' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'doctor_visit_summary' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'cancellation_reason' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'status' => ['sometimes', 'required', Rule::enum(AppointmentStatus::class)],
        ];
    }
}
