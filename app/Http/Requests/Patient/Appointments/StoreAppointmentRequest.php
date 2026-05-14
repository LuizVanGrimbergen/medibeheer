<?php

namespace App\Http\Requests\Patient\Appointments;

use App\Enums\AppointmentStatus;
use App\Enums\DoctorType;
use App\Models\Appointment;
use App\Rules\AppointmentStartsAtNotInPast;
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

    protected function prepareForValidation(): void
    {
        $raw = $this->input('house_number');
        $normalized = '';
        if (is_string($raw)) {
            $normalized = trim($raw);
        }

        $patient = $this->user()->patient;

        $merge = [
            'house_number' => $normalized,
        ];

        if ($patient->families()->count() === 0) {
            $merge['needs_transport'] = false;
            $merge['transport_family_ids'] = [];
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        $patient = $this->user()->patient;

        return [
            'doctor_type' => ['required', Rule::enum(DoctorType::class)],
            'provider_name' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:500'],
            'house_number' => ['string', 'max:32'],
            'postal_code' => ['required', 'string', 'min:4', 'max:32'],
            'city' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date', new AppointmentStartsAtNotInPast],
            'needs_transport' => ['sometimes', 'boolean'],
            'transport_family_ids' => Rule::when(
                $this->boolean('needs_transport'),
                ['required', 'array', 'min:1'],
                ['sometimes', 'array'],
            ),
            'transport_family_ids.*' => [
                'integer',
                Rule::exists('family_patient', 'family_id')->where(
                    'patient_id',
                    $patient->id,
                ),
            ],
            'notes' => ['nullable', 'string', 'max:10000'],
            'status' => ['sometimes', Rule::enum(AppointmentStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'postal_code.min' => trans('patient_appointments.postal_code_min'),
        ];
    }
}
