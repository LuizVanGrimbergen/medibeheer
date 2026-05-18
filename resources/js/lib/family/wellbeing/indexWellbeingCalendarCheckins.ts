import type { DailyCheckin, DailyMoodScoreValue } from '@/lib/types';

export function indexWellbeingCalendarCheckins(checkins: DailyCheckin[]): {
    checkinsByDate: Map<string, DailyCheckin>;
    moodsByDate: Record<string, DailyMoodScoreValue>;
} {
    const checkinsByDate = new Map<string, DailyCheckin>();
    const moodsByDate: Record<string, DailyMoodScoreValue> = {};

    for (const checkin of checkins) {
        checkinsByDate.set(checkin.checkin_date, checkin);
        moodsByDate[checkin.checkin_date] = checkin.mood_score;
    }

    return { checkinsByDate, moodsByDate };
}
