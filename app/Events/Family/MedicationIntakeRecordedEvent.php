<?php

declare(strict_types=1);

namespace App\Events\Family;

use App\Models\MedicationIntake;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class MedicationIntakeRecordedEvent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public MedicationIntake $intake,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('patient.'.$this->intake->patient_id.'.family-updates'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'medication-intake.recorded';
    }

    public function broadcastWith(): array
    {
        return [
            'patient_id' => $this->intake->patient_id,
            'intake_id' => $this->intake->id,
            'intake_date' => $this->intake->intake_date->toDateString(),
        ];
    }
}
