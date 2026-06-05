import type { MaybeRefOrGetter } from 'vue';
import { computed, toValue } from 'vue';
import { useI18n } from 'vue-i18n';
import { formatHistoryCalendarLongDate } from '@/lib/history/formatHistoryCalendarDate';
import {
    historyCalendarMonthKey,
    shiftHistoryCalendarDate,
} from '@/lib/history/shiftHistoryCalendarDate';

type SelectedDayController = {
    selectedCalendarDate: { value: string | null };
    selectCalendarDate: (dateKey: string | null, scroll?: boolean) => void;
};

type UseHistorySelectedDayNavigationOptions = {
    calendarMonth: MaybeRefOrGetter<string>;
    selectedDay: SelectedDayController;
    onNavigateMonth?: (month: string, date: string) => void;
};

export function useHistorySelectedDayNavigation(
    options: UseHistorySelectedDayNavigationOptions,
) {
    const { locale } = useI18n();

    const selectedDateLabel = computed((): string => {
        const date = options.selectedDay.selectedCalendarDate.value;

        if (date === null) {
            return '';
        }

        return formatHistoryCalendarLongDate(date, locale.value);
    });

    function shiftSelectedDay(delta: number): void {
        const current = options.selectedDay.selectedCalendarDate.value;

        if (current === null) {
            return;
        }

        const next = shiftHistoryCalendarDate(current, delta);
        const nextMonth = historyCalendarMonthKey(next);
        const currentMonth = toValue(options.calendarMonth);

        if (nextMonth !== currentMonth) {
            options.onNavigateMonth?.(nextMonth, next);

            return;
        }

        options.selectedDay.selectCalendarDate(next);
    }

    function showPreviousDay(): void {
        shiftSelectedDay(-1);
    }

    function showNextDay(): void {
        shiftSelectedDay(1);
    }

    return {
        selectedDateLabel,
        showPreviousDay,
        showNextDay,
    };
}
