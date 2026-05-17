<?php

declare(strict_types=1);

namespace App\Support\Medications;

final class DoseTime
{
    private function __construct(
        public readonly int $hours,
        public readonly int $minutes,
    ) {}

    public static function tryFrom(string $value): ?self
    {
        if (preg_match('/^(\d{1,2}):(\d{2})$/', trim($value), $matches) !== 1) {
            return null;
        }

        $hours = (int) $matches[1];
        $minutes = (int) $matches[2];

        if ($hours > 23 || $minutes > 59) {
            return null;
        }

        return new self($hours, $minutes);
    }

    public function minutesSinceMidnight(): int
    {
        return $this->hours * 60 + $this->minutes;
    }
}
