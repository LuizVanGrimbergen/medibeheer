import type {
    TodayMedicationIntakeDayPeriodValue,
    TodayMedicationIntakeSlot,
} from '@/lib/types';

export const TODAY_MEDICATION_INTAKE_DAY_PERIOD_ORDER = [
    'morning',
    'afternoon',
    'evening',
    'night',
] as const satisfies readonly TodayMedicationIntakeDayPeriodValue[];

export type TodayMedicationIntakeDayPeriodGroup = {
    period: TodayMedicationIntakeDayPeriodValue;
    slots: TodayMedicationIntakeSlot[];
};

export type TodayMedicationIntakeDashboardGroups = {
    periodGroups: TodayMedicationIntakeDayPeriodGroup[];
    takenSlots: TodayMedicationIntakeSlot[];
};

export type TodayMedicationIntakePeriodSection = {
    period: TodayMedicationIntakeDayPeriodValue;
    pendingSlots: TodayMedicationIntakeSlot[];
};

function minutesSinceMidnight(value: string): number {
    const match = /^(\d{1,2}):(\d{2})$/.exec(value.trim());

    if (match === null) {
        return 24 * 60;
    }

    const hours = Number(match[1]);
    const minutes = Number(match[2]);

    if (hours > 23 || minutes > 59) {
        return 24 * 60;
    }

    return hours * 60 + minutes;
}

function dayPeriodSortRank(
    period: TodayMedicationIntakeDayPeriodValue,
): number {
    const rank = TODAY_MEDICATION_INTAKE_DAY_PERIOD_ORDER.indexOf(period);

    return rank === -1 ? TODAY_MEDICATION_INTAKE_DAY_PERIOD_ORDER.length : rank;
}

export function compareTodayMedicationIntakeSlots(
    left: TodayMedicationIntakeSlot,
    right: TodayMedicationIntakeSlot,
): number {
    const periodCompare =
        dayPeriodSortRank(left.day_period) -
        dayPeriodSortRank(right.day_period);

    if (periodCompare !== 0) {
        return periodCompare;
    }

    return (
        minutesSinceMidnight(left.dose_time) -
        minutesSinceMidnight(right.dose_time)
    );
}

export function groupTodayMedicationIntakesByDayPeriod(
    slots: readonly TodayMedicationIntakeSlot[],
): TodayMedicationIntakeDayPeriodGroup[] {
    const buckets = new Map<
        TodayMedicationIntakeDayPeriodValue,
        TodayMedicationIntakeSlot[]
    >();

    for (const period of TODAY_MEDICATION_INTAKE_DAY_PERIOD_ORDER) {
        buckets.set(period, []);
    }

    for (const slot of slots) {
        buckets.get(slot.day_period)?.push(slot);
    }

    return TODAY_MEDICATION_INTAKE_DAY_PERIOD_ORDER.flatMap((period) => {
        const periodSlots = buckets.get(period) ?? [];

        if (periodSlots.length < 1) {
            return [];
        }

        return [{ period, slots: periodSlots }];
    });
}

export function buildTodayMedicationIntakePeriodSections(
    groups: TodayMedicationIntakeDashboardGroups,
): TodayMedicationIntakePeriodSection[] {
    return groups.periodGroups.flatMap((group) => {
        if (group.slots.length < 1) {
            return [];
        }

        return [{ period: group.period, pendingSlots: group.slots }];
    });
}

export function partitionTodayMedicationIntakes(
    slots: readonly TodayMedicationIntakeSlot[],
): TodayMedicationIntakeDashboardGroups {
    const pending: TodayMedicationIntakeSlot[] = [];
    const taken: TodayMedicationIntakeSlot[] = [];

    for (const slot of slots) {
        if (slot.taken_at !== null) {
            taken.push(slot);
        } else {
            pending.push(slot);
        }
    }

    pending.sort(compareTodayMedicationIntakeSlots);
    taken.sort(compareTodayMedicationIntakeSlots);

    return {
        periodGroups: groupTodayMedicationIntakesByDayPeriod(pending),
        takenSlots: taken,
    };
}
