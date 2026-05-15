import {
    inclusiveCalendarDaysBetweenIsoDates,
    medicationScheduleEndDateIsoInclusiveLocal,
    todayLocalIsoDate,
} from '@/lib/patient/medications/schedule/medicationScheduleDuration';

export const MEDICATION_SCHEDULE_DURATION_PRESET_KEYS = [
    '1_week',
    '4_weeks',
    '6_weeks',
] as const;

export type MedicationScheduleDurationPresetKey =
    (typeof MEDICATION_SCHEDULE_DURATION_PRESET_KEYS)[number];

export type MedicationScheduleDurationChoice =
    | MedicationScheduleDurationPresetKey
    | 'custom';

const PRESET_INCLUSIVE_DAYS: Record<MedicationScheduleDurationPresetKey, number> = {
    '1_week': 7,
    '4_weeks': 28,
    '6_weeks': 42,
};

export function detectMedicationScheduleDurationPreset(
    startDate: string,
    endDate: string,
): MedicationScheduleDurationChoice {
    const spanDays = inclusiveCalendarDaysBetweenIsoDates(
        startDate.trim(),
        endDate.trim(),
    );

    if (spanDays === null) {
        return 'custom';
    }

    for (const preset of MEDICATION_SCHEDULE_DURATION_PRESET_KEYS) {
        if (spanDays === PRESET_INCLUSIVE_DAYS[preset]) {
            return preset;
        }
    }

    return 'custom';
}

export function applyMedicationScheduleDurationPreset(
    schedule: { start_date: string; end_date: string },
    preset: MedicationScheduleDurationPresetKey,
): void {
    const start =
        schedule.start_date.trim().length > 0
            ? schedule.start_date.trim()
            : todayLocalIsoDate();
    const end = medicationScheduleEndDateIsoInclusiveLocal(
        start,
        PRESET_INCLUSIVE_DAYS[preset],
    );

    if (end === null) {
        return;
    }

    schedule.start_date = start;
    schedule.end_date = end;
}
