import type { DailyCheckin, FamilyDashboardProps, Paginated } from '@/lib/types';

export type FamilyWellbeingScreenProps = {
    family?: FamilyDashboardProps;
    wellbeing_calendar_month: string;
    wellbeing_calendar_checkins?: DailyCheckin[];
    wellbeing_checkins?: Paginated<DailyCheckin>;
};
