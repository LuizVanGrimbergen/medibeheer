import {
    doctorPatientAdherenceProgressPercent,
    resolveDoctorPatientAdherenceTone,
    type DoctorPatientAdherenceTone,
} from '@/lib/doctor/patients/doctorPatientAdherenceTone';
import { isNotableDailyMood } from '@/lib/mood/isNotableDailyMood';
import type { MedicationIntakeDayIconStatusValue } from '@/lib/patient/medications/history/medicationIntakeDayPresentation';
import type { MedicationIntakeCalendarDay } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';
import type { DailyCheckin, DailyMoodScoreValue } from '@/lib/types';

export type DoctorPatientMedicationSnapshot = {
    scheduledDays: number;
    completeDays: number;
    missedDays: number;
    statusCounts: Record<MedicationIntakeDayIconStatusValue, number>;
    adherenceTone: DoctorPatientAdherenceTone;
    progressPercent: number;
};

export type DoctorPatientWellbeingSnapshot = {
    checkinCount: number;
    notableDayCount: number;
    moodCounts: Record<DailyMoodScoreValue, number>;
    lastCheckin: {
        date: string;
        mood: DailyMoodScoreValue;
    } | null;
};

export type DoctorPatientOverviewSnapshot = {
    medication: DoctorPatientMedicationSnapshot;
    wellbeing: DoctorPatientWellbeingSnapshot;
};

export function buildDoctorPatientOverviewSnapshot(
    medicationDays: MedicationIntakeCalendarDay[],
    wellbeingCheckins: DailyCheckin[],
): DoctorPatientOverviewSnapshot {
    const scheduledDays = medicationDays.filter(
        (day) => day.status !== 'no_schedule',
    );
    const completeDays = scheduledDays.filter(
        (day) => day.status === 'complete',
    );
    const missedDays = scheduledDays.filter(
        (day) => day.status === 'partial' || day.status === 'none_taken',
    );

    const statusCounts: Record<MedicationIntakeDayIconStatusValue, number> = {
        complete: 0,
        partial: 0,
        none_taken: 0,
    };

    for (const day of scheduledDays) {
        if (
            day.status === 'complete' ||
            day.status === 'partial' ||
            day.status === 'none_taken'
        ) {
            statusCounts[day.status] += 1;
        }
    }

    const sortedCheckins = [...wellbeingCheckins].sort((left, right) =>
        right.checkin_date.localeCompare(left.checkin_date),
    );

    const notableDayCount = wellbeingCheckins.filter((checkin) =>
        isNotableDailyMood(checkin.mood_score),
    ).length;

    const moodCounts: Record<DailyMoodScoreValue, number> = {
        bad: 0,
        ok: 0,
        good: 0,
    };

    for (const checkin of wellbeingCheckins) {
        moodCounts[checkin.mood_score] += 1;
    }

    const lastCheckin = sortedCheckins[0];

    return {
        medication: {
            scheduledDays: scheduledDays.length,
            completeDays: completeDays.length,
            missedDays: missedDays.length,
            statusCounts,
            adherenceTone: resolveDoctorPatientAdherenceTone(
                completeDays.length,
                scheduledDays.length,
            ),
            progressPercent: doctorPatientAdherenceProgressPercent(
                completeDays.length,
                scheduledDays.length,
            ),
        },
        wellbeing: {
            checkinCount: wellbeingCheckins.length,
            notableDayCount,
            moodCounts,
            lastCheckin:
                lastCheckin === undefined
                    ? null
                    : {
                          date: lastCheckin.checkin_date,
                          mood: lastCheckin.mood_score,
                      },
        },
    };
}
