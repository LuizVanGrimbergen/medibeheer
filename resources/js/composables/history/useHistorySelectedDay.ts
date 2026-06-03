import type { MaybeRefOrGetter } from 'vue';
import { nextTick, ref, toValue, watch } from 'vue';

type HistorySelectedDayScrollTarget = {
    scrollIntoView: (options?: ScrollIntoViewOptions) => void;
};

export function useHistorySelectedDay(calendarMonth: MaybeRefOrGetter<string>) {
    const selectedCalendarDate = ref<string | null>(null);
    const selectedDaySectionRef = ref<HistorySelectedDayScrollTarget | null>(
        null,
    );

    watch(
        () => toValue(calendarMonth),
        () => {
            selectedCalendarDate.value = null;
        },
    );

    function onSelectCalendarDate(dateKey: string): void {
        const next = selectedCalendarDate.value === dateKey ? null : dateKey;

        selectedCalendarDate.value = next;

        if (next === null) {
            return;
        }

        nextTick(() => {
            selectedDaySectionRef.value?.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });
        });
    }

    return {
        selectedCalendarDate,
        selectedDaySectionRef,
        onSelectCalendarDate,
    };
}
