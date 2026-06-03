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

export const MEDICATION_SCHEDULE_DURATION_ONGOING_KEY = 'ongoing' as const;

export type MedicationScheduleDurationTimedPresetKey =
    (typeof MEDICATION_SCHEDULE_DURATION_PRESET_KEYS)[number];

export type MedicationScheduleDurationOngoingPresetKey =
    typeof MEDICATION_SCHEDULE_DURATION_ONGOING_KEY;

export type MedicationScheduleDurationPresetKey =
    | MedicationScheduleDurationTimedPresetKey
    | MedicationScheduleDurationOngoingPresetKey;

export const MEDICATION_SCHEDULE_DURATION_UI_PRESET_KEYS = [
    ...MEDICATION_SCHEDULE_DURATION_PRESET_KEYS,
    MEDICATION_SCHEDULE_DURATION_ONGOING_KEY,
] as const;

export type MedicationScheduleDurationChoice =
    | MedicationScheduleDurationPresetKey
    | 'custom';

const PRESET_INCLUSIVE_DAYS: Record<
    MedicationScheduleDurationTimedPresetKey,
    number
> = {
    '1_week': 7,
    '4_weeks': 28,
    '6_weeks': 42,
};

export function isMedicationScheduleOngoing(
    startDate: string,
    endDate: string,
): boolean {
    const endTrimmed = endDate.trim();

    if (endTrimmed.length > 0) {
        return false;
    }

    const startTrimmed = startDate.trim();

    return (
        startTrimmed.length > 0 &&
        medicationScheduleEndDateIsoInclusiveLocal(startTrimmed, 1) !== null
    );
}

export function detectMedicationScheduleDurationPreset(
    startDate: string,
    endDate: string,
): MedicationScheduleDurationChoice | null {
    const startTrimmed = startDate.trim();
    const endTrimmed = endDate.trim();

    if (startTrimmed.length < 1 && endTrimmed.length < 1) {
        return null;
    }

    if (isMedicationScheduleOngoing(startTrimmed, endTrimmed)) {
        return MEDICATION_SCHEDULE_DURATION_ONGOING_KEY;
    }

    const spanDays = inclusiveCalendarDaysBetweenIsoDates(
        startTrimmed,
        endTrimmed,
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
    preset: MedicationScheduleDurationTimedPresetKey,
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

export function applyMedicationScheduleOngoingPreset(schedule: {
    start_date: string;
    end_date: string;
}): void {
    schedule.start_date =
        schedule.start_date.trim().length > 0
            ? schedule.start_date.trim()
            : todayLocalIsoDate();
    schedule.end_date = '';
}
