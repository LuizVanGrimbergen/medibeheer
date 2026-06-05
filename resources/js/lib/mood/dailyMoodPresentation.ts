import type { LucideIcon } from 'lucide-vue-next';
import { Frown, Meh, Smile } from 'lucide-vue-next';
import type { DailyMoodScoreValue } from '@/lib/types';
import { DAILY_MOOD_SCORE_VALUES } from '@/lib/types';

export type DailyMoodPresentation = {
    mood: DailyMoodScoreValue;
    icon: LucideIcon;
    faceClass: string;
    iconBackgroundClass: string;
    labelKey: `family.wellbeing.mood.${DailyMoodScoreValue}`;
};

const DAILY_MOOD_ICON: Record<DailyMoodScoreValue, LucideIcon> = {
    bad: Frown,
    ok: Meh,
    good: Smile,
};

type DailyMoodPresentationFields = Pick<
    DailyMoodPresentation,
    'faceClass' | 'iconBackgroundClass' | 'labelKey'
>;

const DAILY_MOOD_PRESENTATION: Record<
    DailyMoodScoreValue,
    DailyMoodPresentationFields
> = {
    bad: {
        faceClass: 'text-danger',
        iconBackgroundClass: 'bg-danger/10',
        labelKey: 'family.wellbeing.mood.bad',
    },
    ok: {
        faceClass: 'text-warning',
        iconBackgroundClass: 'bg-warning/10',
        labelKey: 'family.wellbeing.mood.ok',
    },
    good: {
        faceClass: 'text-success',
        iconBackgroundClass: 'bg-success/10',
        labelKey: 'family.wellbeing.mood.good',
    },
};

export const dailyMoodIcon = (mood: DailyMoodScoreValue): LucideIcon =>
    DAILY_MOOD_ICON[mood];

export const dailyMoodFaceClass = (mood: DailyMoodScoreValue): string =>
    DAILY_MOOD_PRESENTATION[mood].faceClass;

export const dailyMoodPresentation = (
    mood: DailyMoodScoreValue,
): DailyMoodPresentation => ({
    mood,
    icon: DAILY_MOOD_ICON[mood],
    ...DAILY_MOOD_PRESENTATION[mood],
});

export const DAILY_MOOD_OPTIONS: DailyMoodPresentation[] =
    DAILY_MOOD_SCORE_VALUES.map((mood) => dailyMoodPresentation(mood));
