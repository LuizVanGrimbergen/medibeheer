import type { Ref } from 'vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import type { HistoryMonthCalendarCell } from '@/lib/history/historyMonthCalendarTypes';

export function useHistoryMonthCalendarGrid(calendarMonth: Ref<string>) {
    const { locale } = useI18n();

    const parsedMonth = computed((): { year: number; month: number } => {
        const parts = calendarMonth.value.split('-').map(Number);
        const year = parts[0];
        const month = parts[1];

        if (
            year === undefined ||
            month === undefined ||
            Number.isNaN(year) ||
            Number.isNaN(month) ||
            month < 1 ||
            month > 12
        ) {
            const now = new Date();

            return {
                year: now.getFullYear(),
                month: now.getMonth() + 1,
            };
        }

        return { year, month };
    });

    const monthTitle = computed((): string => {
        const { year, month } = parsedMonth.value;
        const loc = locale.value === 'nl' ? 'nl-NL' : undefined;

        return new Intl.DateTimeFormat(loc, {
            month: 'long',
            year: 'numeric',
        }).format(new Date(year, month - 1, 1));
    });

    const weekdayLabels = computed((): string[] => {
        const loc = locale.value === 'nl' ? 'nl-NL' : undefined;
        const fmt = new Intl.DateTimeFormat(loc, { weekday: 'short' });

        return Array.from({ length: 7 }, (_, index) =>
            fmt.format(new Date(2024, 0, 1 + index)),
        );
    });

    const calendarWeeks = computed((): HistoryMonthCalendarCell[][] => {
        const { year, month } = parsedMonth.value;
        const lastDay = new Date(year, month, 0).getDate();
        const mondayOffset = (new Date(year, month - 1, 1).getDay() + 6) % 7;

        const cells: HistoryMonthCalendarCell[] = [];

        for (let i = 0; i < mondayOffset; i++) {
            cells.push({
                dateKey: null,
                dayNum: null,
            });
        }

        for (let day = 1; day <= lastDay; day++) {
            cells.push({
                dateKey: `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`,
                dayNum: day,
            });
        }

        while (cells.length % 7 !== 0) {
            cells.push({
                dateKey: null,
                dayNum: null,
            });
        }

        const weeks: HistoryMonthCalendarCell[][] = [];

        for (let i = 0; i < cells.length; i += 7) {
            weeks.push(cells.slice(i, i + 7));
        }

        return weeks;
    });

    function shiftMonth(delta: number): string {
        const { year, month } = parsedMonth.value;
        const next = new Date(year, month - 1 + delta, 1);

        return `${next.getFullYear()}-${String(next.getMonth() + 1).padStart(2, '0')}`;
    }

    return {
        parsedMonth,
        monthTitle,
        weekdayLabels,
        calendarWeeks,
        shiftMonth,
    };
}
