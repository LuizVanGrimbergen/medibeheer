<?php

namespace App\Http\Requests\Family;

use App\Services\FamilyInvitationService;
use Illuminate\Foundation\Http\FormRequest;

class AcceptFamilyInvitationRequest extends FormRequest
{
    /**************************************/
    /*           Authorization */
    /**************************************/
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isFamilyMember();
    }

    /**************************************/
    /*          Validation Rules */
    /**************************************/
    
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'size:40', 'regex:/^[a-fA-F0-9]+$/'],
        ];
    }

    public function normalizedCode(): string
    {
        return FamilyInvitationService::normalizeInviteCode($this->validated('code'));
    }
}
