<?php

declare(strict_types=1);

namespace App\Support\Medications;

use Carbon\CarbonInterface;

final class MedicationScheduleDoseTimes
{
    public const int DEFAULT_SNOOZE_MINUTES = 30;

    public const int MAX_SNOOZE_MINUTES = 24 * 60;

    public static function parse(string $raw): array
    {
        $trimmed = trim($raw);

        if ($trimmed === '') {
            return [];
        }

        $slots = [];

        foreach (explode(',', $trimmed) as $segment) {
            $part = trim($segment);

            if ($part === '') {
                continue;
            }

            $time = $part;
            $snoozeMinutes = self::DEFAULT_SNOOZE_MINUTES;

            if (str_contains($part, '|')) {
                [$timePart, $snoozePart] = array_pad(explode('|', $part, 2), 2, '');
                $time = trim($timePart);
                $parsedSnooze = self::parseSnoozeMinutes(trim($snoozePart));

                if ($parsedSnooze !== null) {
                    $snoozeMinutes = $parsedSnooze;
                }
            }

            if ($time === '') {
                continue;
            }

            $slots[] = [
                'time' => $time,
                'snooze_minutes' => $snoozeMinutes,
            ];
        }

        return $slots;
    }

    public static function encode(array $slots): string
    {
        if ($slots === []) {
            return '';
        }

        $segments = [];

        foreach ($slots as $slot) {
            $time = trim($slot['time']);

            if ($time === '') {
                continue;
            }

            $snooze = self::clampSnoozeMinutes($slot['snooze_minutes']);
            $segments[] = "{$time}|{$snooze}";
        }

        return implode(', ', $segments);
    }

    /** @return list<string> */
    public static function sortedTimes(string $raw): array
    {
        $seen = [];

        foreach (self::parse($raw) as $slot) {
            $seen[$slot['time']] = true;
        }

        if ($seen === []) {
            return [''];
        }

        $times = array_keys($seen);

        usort($times, static function (string $left, string $right): int {
            $leftTime = DoseTime::tryFrom($left);
            $rightTime = DoseTime::tryFrom($right);

            if ($leftTime === null && $rightTime === null) {
                return strcmp($left, $right);
            }

            if ($leftTime === null) {
                return 1;
            }

            if ($rightTime === null) {
                return -1;
            }

            return $leftTime->minutesSinceMidnight() <=> $rightTime->minutesSinceMidnight();
        });

        return $times;
    }

    public static function displayTimes(string $raw): string
    {
        $times = array_map(
            static fn (array $slot): string => $slot['time'],
            self::parse($raw),
        );

        if ($times === []) {
            return '';
        }

        return implode(', ', $times);
    }

    public static function displaySnoozeMinutes(string $raw): string
    {
        $snoozes = array_map(
            static fn (array $slot): string => (string) self::clampSnoozeMinutes($slot['snooze_minutes']),
            self::parse($raw),
        );

        if ($snoozes === []) {
            return '';
        }

        return implode(', ', $snoozes);
    }

    public static function snoozeMinutesFor(string $doseTime, string $raw): int
    {
        $needle = trim($doseTime);

        foreach (self::parse($raw) as $slot) {
            if ($slot['time'] === $needle) {
                return self::clampSnoozeMinutes($slot['snooze_minutes']);
            }
        }

        return self::DEFAULT_SNOOZE_MINUTES;
    }

    public static function mergeDisplayTimesAndSnoozes(string $doseTime, string $snoozeTime): string
    {
        $timeSegments = array_values(array_filter(
            array_map(static fn (string $segment): string => trim($segment), explode(',', $doseTime)),
            static fn (string $segment): bool => $segment !== '',
        ));
        $snoozeSegments = array_values(array_filter(
            array_map(static fn (string $segment): string => trim($segment), explode(',', $snoozeTime)),
            static fn (string $segment): bool => $segment !== '',
        ));

        $slots = [];

        foreach ($timeSegments as $index => $time) {
            $snoozeRaw = $snoozeSegments[$index] ?? '';
            $parsedSnooze = self::parseSnoozeMinutes($snoozeRaw);

            $slots[] = [
                'time' => $time,
                'snooze_minutes' => $parsedSnooze ?? self::DEFAULT_SNOOZE_MINUTES,
            ];
        }

        return self::encode($slots);
    }

    public static function resolveIntakeWindowState(
        string $doseTime,
        string $raw,
        ?CarbonInterface $now = null,
    ): string {
        $now = $now ?? MedicationIntakeClock::now();

        if (self::isWithinIntakeWindow($doseTime, $raw, $now)) {
            return 'within';
        }

        $parsed = DoseTime::tryFrom($doseTime);

        if ($parsed === null) {
            return 'within';
        }

        $start = $now->copy()->startOfDay()->addMinutes($parsed->minutesSinceMidnight());

        if ($now->lt($start)) {
            return 'before';
        }

        return 'past';
    }

    public static function isWithinIntakeWindow(string $doseTime, string $raw, ?CarbonInterface $now = null): bool
    {
        $now = $now ?? MedicationIntakeClock::now();
        $parsed = DoseTime::tryFrom($doseTime);

        if ($parsed === null) {
            return false;
        }

        $start = $now->copy()->startOfDay()->addMinutes($parsed->minutesSinceMidnight());
        $end = $start->copy()->addMinutes(self::snoozeMinutesFor($doseTime, $raw));

        return $now->greaterThanOrEqualTo($start) && $now->lessThanOrEqualTo($end);
    }

    private static function parseSnoozeMinutes(string $value): ?int
    {
        if ($value === '' || preg_match('/^\d+$/', $value) !== 1) {
            return null;
        }

        return self::clampSnoozeMinutes((int) $value);
    }

    private static function clampSnoozeMinutes(int $minutes): int
    {
        if ($minutes < 0) {
            return 0;
        }

        if ($minutes > self::MAX_SNOOZE_MINUTES) {
            return self::MAX_SNOOZE_MINUTES;
        }

        return $minutes;
    }
}
