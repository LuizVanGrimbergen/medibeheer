<?php

declare(strict_types=1);

namespace App\Support\Medications;

use Carbon\CarbonInterface;

final class MedicationScheduleDoseTimes
{
    public const int DEFAULT_SNOOZE_MINUTES = 30;

    public const int MAX_SNOOZE_MINUTES = 24 * 60;

    /** @return list<array{time: string, snooze_minutes: int}> */
    public static function parse(string $doseTimeRaw, ?string $snoozeTimeRaw = null): array
    {
        $timeSegments = self::timeSegments($doseTimeRaw);

        if ($timeSegments === []) {
            return [];
        }

        $snoozeSegments = self::snoozeSegments($snoozeTimeRaw);
        $slots = [];

        foreach ($timeSegments as $index => $time) {
            $snoozeRaw = $snoozeSegments[$index] ?? '';
            $parsedSnooze = self::parseSnoozeMinutes($snoozeRaw);

            $slots[] = [
                'time' => $time,
                'snooze_minutes' => $parsedSnooze ?? self::DEFAULT_SNOOZE_MINUTES,
            ];
        }

        return $slots;
    }

    /** @return list<string> */
    public static function timeSegments(string $doseTimeRaw): array
    {
        $trimmed = trim($doseTimeRaw);

        if ($trimmed === '') {
            return [];
        }

        $segments = [];

        foreach (explode(',', $trimmed) as $segment) {
            $part = trim($segment);

            if ($part !== '') {
                $segments[] = $part;
            }
        }

        return $segments;
    }

    /** @return list<string> */
    public static function sortedTimes(string $doseTimeRaw, ?string $snoozeTimeRaw = null): array
    {
        $seen = [];

        foreach (self::parse($doseTimeRaw, $snoozeTimeRaw) as $slot) {
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

    public static function displaySnoozeMinutes(string $snoozeTimeRaw): string
    {
        $trimmed = trim($snoozeTimeRaw);

        if ($trimmed === '') {
            return '';
        }

        $snoozes = array_map(
            static function (string $segment): string {
                $parsed = self::parseSnoozeMinutes(trim($segment));

                return (string) ($parsed ?? self::DEFAULT_SNOOZE_MINUTES);
            },
            explode(',', $trimmed),
        );

        return implode(', ', array_values(array_filter(
            $snoozes,
            static fn (string $segment): bool => $segment !== '',
        )));
    }

    public static function snoozeMinutesFor(string $doseTime, string $doseTimeRaw, ?string $snoozeTimeRaw = null): int
    {
        $needle = trim($doseTime);

        foreach (self::parse($doseTimeRaw, $snoozeTimeRaw) as $slot) {
            if ($slot['time'] === $needle) {
                return self::clampSnoozeMinutes($slot['snooze_minutes']);
            }
        }

        return self::DEFAULT_SNOOZE_MINUTES;
    }

    public static function resolveIntakeWindowState(
        string $doseTime,
        string $doseTimeRaw,
        ?string $snoozeTimeRaw = null,
        ?CarbonInterface $now = null,
    ): string {
        $now = $now ?? MedicationIntakeClock::now();

        if (self::isWithinIntakeWindow($doseTime, $doseTimeRaw, $snoozeTimeRaw, $now)) {
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

    public static function isWithinIntakeWindow(
        string $doseTime,
        string $doseTimeRaw,
        ?string $snoozeTimeRaw = null,
        ?CarbonInterface $now = null,
    ): bool {
        $now = $now ?? MedicationIntakeClock::now();
        $parsed = DoseTime::tryFrom($doseTime);

        if ($parsed === null) {
            return false;
        }

        $start = $now->copy()->startOfDay()->addMinutes($parsed->minutesSinceMidnight());
        $end = $start->copy()->addMinutes(self::snoozeMinutesFor($doseTime, $doseTimeRaw, $snoozeTimeRaw));

        return $now->greaterThanOrEqualTo($start) && $now->lessThanOrEqualTo($end);
    }

    /** @return list<string> */
    private static function snoozeSegments(?string $snoozeTimeRaw): array
    {
        $trimmed = trim($snoozeTimeRaw ?? '');

        if ($trimmed === '') {
            return [];
        }

        return array_values(array_filter(
            array_map(static fn (string $segment): string => trim($segment), explode(',', $trimmed)),
            static fn (string $segment): bool => $segment !== '',
        ));
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
