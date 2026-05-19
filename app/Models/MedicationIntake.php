<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Medications\MedicationDoseTimeBlindIndex;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationIntake extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'medication_id',
        'medication_schedule_id',
        'intake_date',
        'dose_time',
        'taken_at',
    ];

    protected function casts(): array
    {
        return [
            'intake_date' => 'date',
            'dose_time' => 'encrypted',
            'taken_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $intake): void {
            $intake->dose_time_index = MedicationDoseTimeBlindIndex::hash(
                (string) $intake->dose_time,
            );
        });
    }

    public static function firstOrNewForScheduleDateAndDoseTime(
        int $medicationScheduleId,
        CarbonInterface|string $intakeDate,
        string $doseTime,
    ): self {
        $intakeDateString = $intakeDate instanceof CarbonInterface
            ? $intakeDate->toDateString()
            : $intakeDate;
        $normalizedDoseTime = MedicationDoseTimeBlindIndex::normalize($doseTime);

        $existing = static::query()
            ->where('medication_schedule_id', $medicationScheduleId)
            ->whereDate('intake_date', '=', $intakeDateString, 'and')
            ->where('dose_time_index', MedicationDoseTimeBlindIndex::hash($normalizedDoseTime))
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return new self([
            'medication_schedule_id' => $medicationScheduleId,
            'intake_date' => $intakeDateString,
            'dose_time' => $normalizedDoseTime,
        ]);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(MedicationSchedule::class, 'medication_schedule_id');
    }
}
