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
        $rawHouseNumber = $this->input('house_number');
        $houseNumber = '';
        if (is_string($rawHouseNumber)) {
            $houseNumber = trim($rawHouseNumber);
        }

        $rawProviderName = $this->input('provider_name');
        $providerName = '';
        if (is_string($rawProviderName)) {
            $providerName = trim($rawProviderName);
        }

        $patient = $this->user()->patient;

        $street = '';
        $rawStreet = $this->input('street');
        if (is_string($rawStreet)) {
            $street = trim($rawStreet);
        }

        $postalCode = '';
        $rawPostalCode = $this->input('postal_code');
        if (is_string($rawPostalCode)) {
            $postalCode = trim($rawPostalCode);
        }

        $city = '';
        $rawCity = $this->input('city');
        if (is_string($rawCity)) {
            $city = trim($rawCity);
        }

        $merge = [
            'house_number' => $houseNumber,
            'provider_name' => $providerName,
            'street' => $street,
            'postal_code' => $postalCode,
            'city' => $city,
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
            'provider_name' => ['nullable', 'string', 'max:255'],
            'street' => Rule::when(
                $this->boolean('needs_transport'),
                ['required', 'string', 'max:500'],
                ['nullable', 'string', 'max:500'],
            ),
            'house_number' => ['string', 'max:32'],
            'postal_code' => Rule::when(
                $this->boolean('needs_transport'),
                ['required', 'string', 'min:4', 'max:32'],
                ['nullable', 'string', 'max:32'],
            ),
            'city' => Rule::when(
                $this->boolean('needs_transport'),
                ['required', 'string', 'max:255'],
                ['nullable', 'string', 'max:255'],
            ),
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
