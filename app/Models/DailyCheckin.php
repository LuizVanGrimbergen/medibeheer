<?php

namespace App\Models;

use App\Enums\DailyCheckinSymptom;
use App\Enums\DailyMoodScore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyCheckin extends Model
{
    use HasFactory;

    /**************************************/
    /*             Attributes */
    /**************************************/

    protected $fillable = [
        'patient_id',
        'checkin_date',
        'mood_score',
        'note',
        'encouragement_message',
    ];

    protected function casts(): array
    {
        return [
            'checkin_date' => 'date',
            'mood_score' => DailyMoodScore::class,
            'note' => 'encrypted',
            'encouragement_message' => 'encrypted',
        ];
    }

    /**************************************/
    /*           Relationships */
    /**************************************/
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function selectedSymptoms(): HasMany
    {
        return $this->hasMany(DailyCheckinSymptomEntry::class)->orderBy('id');
    }

    /**************************************/
    /*       Accessors / Mutators */
    /**************************************/

    /**************************************/
    /*              Scopes */
    /**************************************/

    /**************************************/
    /*              Helpers */
    /**************************************/

    public function symptomValues(): array
    {
        $this->loadMissing('selectedSymptoms');

        return $this->selectedSymptoms
            ->pluck('symptom')
            ->map(fn (DailyCheckinSymptom $symptom) => $symptom->value)
            ->values()
            ->all();
    }

    public function toDashboardPayload(): array
    {
        $values = $this->symptomValues();

        $symptoms = null;

        if ($this->mood_score !== DailyMoodScore::GOOD) {
            $symptoms = $values === [] ? null : $values;
        }

        return [
            'id' => $this->id,
            'checkin_date' => $this->checkin_date->toDateString(),
            'mood_score' => $this->mood_score->value,
            'symptoms' => $symptoms,
            'note' => $this->note,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
