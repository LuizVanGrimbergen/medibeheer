<?php

namespace App\Http\Requests\Family\MedicationPlans;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class PublishFamilyMedicationPlanProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isFamilyMember() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $email = $this->input('patient_email');

        if (is_string($email)) {
            $this->merge([
                'patient_email' => User::normalizeEmail($email),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'patient_email' => ['required', 'string', 'email:rfc', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_email.required' => trans('medication_plan_proposal.publish.validation.patient_email_required'),
            'patient_email.email' => trans('medication_plan_proposal.publish.validation.patient_email_invalid'),
        ];
    }

    public function normalizedPatientEmail(): string
    {
        return (string) $this->validated('patient_email');
    }
}
