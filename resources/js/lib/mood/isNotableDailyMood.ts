import type { DailyMoodScoreValue } from '@/lib/types';

export const NOTABLE_DAILY_MOOD_SCORE_VALUES = [
    'bad',
    'ok',
] as const satisfies readonly DailyMoodScoreValue[];

export function isNotableDailyMood(mood: DailyMoodScoreValue): boolean {
    return mood === 'bad' || mood === 'ok';
}
