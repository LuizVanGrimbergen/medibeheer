<?php

namespace App\Http\Requests\Patient;

use App\Enums\DailyCheckinSymptom;
use App\Enums\DailyMoodScore;
use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDailyCheckinRequest extends FormRequest
{
    /**************************************/
    /*           Authorization */
    /**************************************/
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null || ! $user->isPatient()) {
            return false;
        }

        $patient = $user->patient;

        if (! $patient instanceof Patient) {
            return false;
        }

        return $this->user()?->can('update', $patient) ?? false;
    }

    /**************************************/
    /*          Validation Rules */
    /**************************************/
    public function rules(): array
    {
        $moodScore = $this->string('mood_score')->toString();
        $allowsSymptoms = in_array($moodScore, [
            DailyMoodScore::BAD->value,
            DailyMoodScore::OK->value,
        ], true);

        return [
            'mood_score' => ['required', Rule::enum(DailyMoodScore::class)],
            'note' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'symptoms' => $allowsSymptoms
                ? ['sometimes', 'nullable', 'array', 'max:10']
                : ['prohibited'],
            'symptoms.*' => $allowsSymptoms
                ? ['distinct', Rule::enum(DailyCheckinSymptom::class)]
                : ['prohibited'],
        ];
    }
}
