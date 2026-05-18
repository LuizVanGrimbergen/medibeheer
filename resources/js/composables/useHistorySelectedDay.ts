import { nextTick, ref, watch, type MaybeRefOrGetter, toValue } from 'vue';

export function useHistorySelectedDay(calendarMonth: MaybeRefOrGetter<string>) {
    const selectedCalendarDate = ref<string | null>(null);
    const selectedDaySectionRef = ref<HTMLElement | null>(null);

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
