<?php

declare(strict_types=1);

namespace App\Support\Family;

final class FamilyMedicationIntakeCalendarSlotPayload
{
    /**
     * @param  list<array<string, mixed>>  $slots
     * @return list<array<string, mixed>>
     */
    public static function collect(array $slots): array
    {
        return array_map(self::fromFullSlot(...), $slots);
    }

    /**
     * @param  array<string, mixed>  $slot
     * @return array<string, mixed>
     */
    public static function fromFullSlot(array $slot): array
    {
        return [
            'medication_schedule_id' => (int) $slot['medication_schedule_id'],
            'dose_time' => (string) $slot['dose_time'],
            'snooze_minutes' => (int) $slot['snooze_minutes'],
            'day_period' => (string) $slot['day_period'],
            'name' => (string) $slot['name'],
            'type_medication' => (string) $slot['type_medication'],
            'taken_at' => filled($slot['taken_at'] ?? null) ? (string) $slot['taken_at'] : null,
            'intake_date' => (string) $slot['intake_date'],
        ];
    }
}
