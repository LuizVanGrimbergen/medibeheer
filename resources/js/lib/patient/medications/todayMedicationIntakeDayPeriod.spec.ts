import { describe, expect, it } from 'vitest';
import {
    buildTodayMedicationIntakePeriodSections,
    partitionTodayMedicationIntakes,
} from '@/lib/patient/medications/todayMedicationIntakeDayPeriod';
import type { TodayMedicationIntakeSlot } from '@/lib/types';

function slot(
    overrides: Partial<TodayMedicationIntakeSlot> &
        Pick<
            TodayMedicationIntakeSlot,
            'day_period' | 'dose_time' | 'taken_at'
        >,
): TodayMedicationIntakeSlot {
    return {
        medication_id: 1,
        medication_schedule_id: 1,
        snooze_minutes: 0,
        intake_window_state: 'within',
        meal_timing: 'unrelated',
        intake_frequency: 'daily',
        intake_weekdays: null,
        name: 'Test',
        type_medication: 'pill',
        dose: null,
        dose_unit: null,
        note: null,
        stocks: [],
        supply_estimate_days: null,
        supply_estimate_quality: 'unknown',
        ...overrides,
    };
}

describe('buildTodayMedicationIntakePeriodSections', () => {
    it('returns only periods with pending slots', () => {
        const groups = partitionTodayMedicationIntakes([
            slot({
                day_period: 'morning',
                dose_time: '08:00',
                taken_at: '2026-06-04T08:05:00+02:00',
            }),
            slot({
                day_period: 'evening',
                dose_time: '20:00',
                taken_at: null,
            }),
        ]);

        expect(buildTodayMedicationIntakePeriodSections(groups)).toEqual([
            {
                period: 'evening',
                pendingSlots: [groups.periodGroups[0].slots[0]],
            },
        ]);
    });

    it('keeps all taken slots on the dashboard groups for a bottom section', () => {
        const takenMorning = slot({
            day_period: 'morning',
            dose_time: '08:00',
            taken_at: '2026-06-04T08:05:00+02:00',
        });
        const takenEvening = slot({
            day_period: 'evening',
            dose_time: '20:00',
            taken_at: '2026-06-04T20:10:00+02:00',
        });

        const groups = partitionTodayMedicationIntakes([
            takenMorning,
            takenEvening,
        ]);

        expect(buildTodayMedicationIntakePeriodSections(groups)).toEqual([]);
        expect(groups.takenSlots).toEqual([takenMorning, takenEvening]);
    });
});
