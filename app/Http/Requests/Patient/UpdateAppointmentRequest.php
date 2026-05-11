<?php

namespace App\Http\Requests\Patient;

use App\Enums\AppointmentStatus;
use App\Enums\DoctorType;
use App\Models\Appointment;
use App\Rules\AppointmentStartsAtNotInPast;
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

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('house_number')) {
            $raw = $this->input('house_number');
            $normalized = '';
            if (is_string($raw)) {
                $normalized = trim($raw);
            }

            $merge['house_number'] = $normalized;
        }

        /** @var Appointment|null $appointment */
        $appointment = $this->route('appointment');

        if (
            $appointment instanceof Appointment
            && $appointment->patient->families()->count() === 0
        ) {
            $merge['needs_transport'] = false;
            $merge['transport_family_ids'] = [];
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        /** @var Appointment $appointment */
        $appointment = $this->route('appointment');

        return [
            'doctor_type' => ['sometimes', 'required', Rule::enum(DoctorType::class)],
            'provider_name' => ['sometimes', 'required', 'string', 'max:255'],
            'street' => ['sometimes', 'required', 'string', 'max:500'],
            'house_number' => ['sometimes', 'string', 'max:32'],
            'postal_code' => ['sometimes', 'required', 'string', 'min:4', 'max:32'],
            'city' => ['sometimes', 'required', 'string', 'max:255'],
            'starts_at' => [
                'sometimes',
                'required',
                'date',
                new AppointmentStartsAtNotInPast($appointment->starts_at),
            ],
            'needs_transport' => ['sometimes', 'required', 'boolean'],
            'transport_family_ids' => Rule::when(
                $this->boolean('needs_transport'),
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

    public function messages(): array
    {
        return [
            'postal_code.min' => trans('patient_appointments.postal_code_min'),
        ];
    }
}
