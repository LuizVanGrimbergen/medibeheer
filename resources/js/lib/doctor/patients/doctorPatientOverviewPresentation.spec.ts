import { describe, expect, it } from 'vitest';
import { buildDoctorPatientAttentionItems } from '@/lib/doctor/patients/buildDoctorPatientAttentionItems';
import { buildDoctorPatientOverviewSnapshot } from '@/lib/doctor/patients/buildDoctorPatientOverviewSnapshot';
import {
    doctorPatientAdherenceProgressPercent,
    resolveDoctorPatientAdherenceTone,
} from '@/lib/doctor/patients/doctorPatientAdherenceTone';
import { filterDoctorPatientMedicationSlots } from '@/lib/doctor/patients/filterDoctorPatientMedicationSlots';
import { filterDoctorPatientWellbeingCheckins } from '@/lib/doctor/patients/filterDoctorPatientWellbeingCheckins';
import type {
    MedicationIntakeCalendarDay,
    MedicationIntakeHistorySlot,
} from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';
import type { DailyCheckin } from '@/lib/types';

describe('doctorPatientAdherenceTone', () => {
    it('marks low adherence as danger', () => {
        expect(resolveDoctorPatientAdherenceTone(2, 31)).toBe('danger');
        expect(doctorPatientAdherenceProgressPercent(2, 31)).toBe(6);
    });

    it('marks strong adherence as success', () => {
        expect(resolveDoctorPatientAdherenceTone(27, 30)).toBe('success');
    });
});

describe('buildDoctorPatientOverviewSnapshot', () => {
    it('summarises medication and wellbeing data for the month', () => {
        const medicationDays: MedicationIntakeCalendarDay[] = [
            {
                date: '2026-05-01',
                status: 'complete',
                scheduled_count: 2,
                taken_count: 2,
            },
            {
                date: '2026-05-02',
                status: 'partial',
                scheduled_count: 2,
                taken_count: 1,
            },
            {
                date: '2026-05-03',
                status: 'no_schedule',
                scheduled_count: 0,
                taken_count: 0,
            },
        ];

        const wellbeingCheckins: DailyCheckin[] = [
            {
                id: 1,
                checkin_date: '2026-05-02',
                mood_score: 'bad',
                symptoms: ['fatigue'],
                note: null,
                created_at: '2026-05-02T08:00:00+00:00',
            },
            {
                id: 2,
                checkin_date: '2026-05-01',
                mood_score: 'good',
                symptoms: null,
                note: null,
                created_at: '2026-05-01T08:00:00+00:00',
            },
        ];

        expect(
            buildDoctorPatientOverviewSnapshot(
                medicationDays,
                wellbeingCheckins,
            ),
        ).toEqual({
            medication: {
                scheduledDays: 2,
                completeDays: 1,
                missedDays: 1,
                statusCounts: {
                    complete: 1,
                    partial: 1,
                    none_taken: 0,
                },
                adherenceTone: 'warning',
                progressPercent: 50,
            },
            wellbeing: {
                checkinCount: 2,
                notableDayCount: 1,
                moodCounts: {
                    bad: 1,
                    ok: 0,
                    good: 1,
                },
                lastCheckin: {
                    date: '2026-05-02',
                    mood: 'bad',
                },
            },
        });
    });
});

describe('filterDoctorPatientMedicationSlots', () => {
    it('returns only slots on days that match the selected status', () => {
        const calendarDays: MedicationIntakeCalendarDay[] = [
            {
                date: '2026-05-01',
                status: 'complete',
                scheduled_count: 1,
                taken_count: 1,
            },
            {
                date: '2026-05-02',
                status: 'partial',
                scheduled_count: 2,
                taken_count: 1,
            },
        ];

        const slots: MedicationIntakeHistorySlot[] = [
            {
                medication_id: 1,
                medication_schedule_id: 10,
                dose_time: '08:00',
                snooze_minutes: 60,
                intake_window_state: 'past',
                day_period: 'morning',
                meal_timing: 'unrelated',
                intake_frequency: 'daily',
                intake_weekdays: null,
                name: 'Magnesium',
                type_medication: 'pill',
                dose: '1',
                dose_unit: 'piece',
                note: null,
                taken_at: '2026-05-01T08:00:00+02:00',
                stocks: [],
                supply_estimate_days: null,
                supply_estimate_quality: 'unknown',
                intake_date: '2026-05-01',
            },
            {
                medication_id: 2,
                medication_schedule_id: 11,
                dose_time: '08:00',
                snooze_minutes: 60,
                intake_window_state: 'past',
                day_period: 'morning',
                meal_timing: 'unrelated',
                intake_frequency: 'daily',
                intake_weekdays: null,
                name: 'Ibuprofen',
                type_medication: 'pill',
                dose: '1',
                dose_unit: 'piece',
                note: null,
                taken_at: null,
                stocks: [],
                supply_estimate_days: null,
                supply_estimate_quality: 'unknown',
                intake_date: '2026-05-02',
            },
        ];

        expect(
            filterDoctorPatientMedicationSlots(slots, calendarDays, 'partial'),
        ).toEqual([slots[1]]);
        expect(
            filterDoctorPatientMedicationSlots(slots, calendarDays, null),
        ).toEqual(slots);
    });
});

describe('filterDoctorPatientWellbeingCheckins', () => {
    it('returns only check-ins that match the selected mood', () => {
        const checkins: DailyCheckin[] = [
            {
                id: 1,
                checkin_date: '2026-05-01',
                mood_score: 'good',
                symptoms: null,
                note: null,
                created_at: '2026-05-01T08:00:00+00:00',
            },
            {
                id: 2,
                checkin_date: '2026-05-02',
                mood_score: 'bad',
                symptoms: null,
                note: null,
                created_at: '2026-05-02T08:00:00+00:00',
            },
        ];

        expect(filterDoctorPatientWellbeingCheckins(checkins, 'good')).toEqual([
            checkins[0],
        ]);
        expect(filterDoctorPatientWellbeingCheckins(checkins, null)).toEqual(
            checkins,
        );
    });
});

describe('buildDoctorPatientAttentionItems', () => {
    it('returns the newest attention items first and limits the list', () => {
        const medicationDays: MedicationIntakeCalendarDay[] = [
            {
                date: '2026-05-01',
                status: 'none_taken',
                scheduled_count: 2,
                taken_count: 0,
            },
        ];

        const wellbeingCheckins: DailyCheckin[] = [
            {
                id: 1,
                checkin_date: '2026-05-03',
                mood_score: 'good',
                symptoms: ['pain'],
                note: null,
                created_at: '2026-05-03T08:00:00+00:00',
            },
            {
                id: 2,
                checkin_date: '2026-05-02',
                mood_score: 'bad',
                symptoms: null,
                note: null,
                created_at: '2026-05-02T08:00:00+00:00',
            },
        ];

        expect(
            buildDoctorPatientAttentionItems(medicationDays, wellbeingCheckins),
        ).toEqual([
            {
                kind: 'wellbeing',
                date: '2026-05-03',
                mood: 'good',
                symptomCount: 1,
            },
            {
                kind: 'wellbeing',
                date: '2026-05-02',
                mood: 'bad',
                symptomCount: 0,
            },
            {
                kind: 'medication',
                date: '2026-05-01',
                status: 'none_taken',
                missedCount: 2,
            },
        ]);
    });
});
