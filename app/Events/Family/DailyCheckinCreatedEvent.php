<?php

declare(strict_types=1);

namespace App\Events\Family;

use App\Models\DailyCheckin;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class DailyCheckinCreatedEvent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public DailyCheckin $checkin,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('patient.'.$this->checkin->patient_id.'.family-updates'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'daily-checkin.created';
    }

    public function broadcastWith(): array
    {
        return [
            'patient_id' => $this->checkin->patient_id,
            'checkin_id' => $this->checkin->id,
            'checkin_date' => $this->checkin->checkin_date->toDateString(),
        ];
    }
}
