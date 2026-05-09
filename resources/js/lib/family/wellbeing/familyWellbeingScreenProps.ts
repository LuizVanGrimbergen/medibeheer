import type { DailyCheckin, Paginated } from '@/lib/types';

export type FamilyWellbeingScreenProps = {
    wellbeing_calendar_month: string;
    wellbeing_calendar_checkins: DailyCheckin[];
    wellbeing_checkins: Paginated<DailyCheckin>;
};
