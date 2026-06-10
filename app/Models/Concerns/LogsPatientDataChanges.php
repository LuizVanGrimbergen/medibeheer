<?php

namespace App\Models\Concerns;

use App\Services\Audit\ActivityLogName;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsPatientDataChanges
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName(ActivityLogName::DATA)
            ->logOnly($this->patientDataActivityLogAttributes())
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $patientId = $this->patientDataActivityPatientId();

        if ($patientId === null) {
            return;
        }

        $activity->properties = $activity->properties->merge([
            'patient_id' => $patientId,
        ]);
    }

    abstract protected function patientDataActivityLogAttributes(): array;

    protected function patientDataActivityPatientId(): ?int
    {
        if (isset($this->patient_id)) {
            return (int) $this->getAttribute('patient_id');
        }

        if (method_exists($this, 'medication')) {
            $this->loadMissing('medication');

            $patientId = $this->medication?->patient_id;

            return $patientId !== null ? (int) $patientId : null;
        }

        return null;
    }
}
