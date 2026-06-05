import type { DailyCheckin, DailyMoodScoreValue } from '@/lib/types';

export function filterDoctorPatientWellbeingCheckins(
    checkins: DailyCheckin[],
    mood: DailyMoodScoreValue | null,
): DailyCheckin[] {
    if (mood === null) {
        return checkins;
    }

    return checkins.filter((checkin) => checkin.mood_score === mood);
}
