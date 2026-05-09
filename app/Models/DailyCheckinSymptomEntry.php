<?php

namespace App\Models;

use App\Enums\DailyCheckinSymptom;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyCheckinSymptomEntry extends Model
{
    protected $table = 'daily_checkin_symptoms';

    protected $fillable = [
        'daily_checkin_id',
        'symptom',
    ];

    protected function casts(): array
    {
        return [
            'symptom' => DailyCheckinSymptom::class,
        ];
    }

    public function dailyCheckin(): BelongsTo
    {
        return $this->belongsTo(DailyCheckin::class);
    }
}
