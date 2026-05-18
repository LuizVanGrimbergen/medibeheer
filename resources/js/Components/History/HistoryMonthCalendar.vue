<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed, toRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { useHistoryMonthCalendarGrid } from '@/composables/useHistoryMonthCalendarGrid';
import type { HistoryMonthCalendarCell } from '@/lib/history/historyMonthCalendarTypes';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        calendarMonth: string;
        selectedDate?: string | null;
        navigateRouteName: string;
        navigateQueryKey?: string;
        gridCaptionKey: string;
        prevMonthAriaLabel: string;
        nextMonthAriaLabel: string;
        openDayDetailsAriaLabel: string;
        dayAriaLabel: (cell: HistoryMonthCalendarCell) => string;
    }>(),
    {
        selectedDate: null,
        navigateQueryKey: 'calendar_month',
    },
);

const emit = defineEmits<{
    selectDate: [dateKey: string];
}>();

const { t } = useI18n();

const calendarMonthRef = toRef(props, 'calendarMonth');

const { monthTitle, weekdayLabels, calendarWeeks, shiftMonth } =
    useHistoryMonthCalendarGrid(calendarMonthRef);

const gridCaption = computed(() =>
    t(props.gridCaptionKey, { month: monthTitle.value }),
);

const preservedQuery = computed((): Record<string, string> => {
    if (typeof globalThis.window === 'undefined') {
        return {};
    }

    const params = new URLSearchParams(globalThis.window.location.search);
    const query: Record<string, string> = {};

    params.forEach((value, key) => {
        if (key !== props.navigateQueryKey) {
            query[key] = value;
        }
    });

    return query;
});

function visitMonth(delta: number): void {
    router.get(
        route(props.navigateRouteName),
        {
            ...preservedQuery.value,
            [props.navigateQueryKey]: shiftMonth(delta),
        },
        { preserveScroll: true },
    );
}

function onDayActivate(cell: HistoryMonthCalendarCell): void {
    if (cell.dateKey === null) {
        return;
    }

    emit('selectDate', cell.dateKey);
}

function dayButtonClass(cell: HistoryMonthCalendarCell): string {
    const selected =
        cell.dateKey !== null && props.selectedDate === cell.dateKey;

    return cn(
        'flex min-h-14 w-full max-w-full min-w-0 flex-col items-center justify-start rounded-lg border px-0.5 py-1 text-center transition-colors sm:min-h-16',
        'border-transparent bg-surface-hover/40 hover:bg-surface-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary',
        selected && 'ring-2 ring-inset ring-primary/40',
    );
}

function dayButtonAriaLabel(cell: HistoryMonthCalendarCell): string {
    return `${props.dayAriaLabel(cell)}. ${props.openDayDetailsAriaLabel}`;
}
</script>

<template>
    <Card class="min-w-0 border-border">
        <CardHeader class="pb-4 pt-6">
            <div class="grid w-full grid-cols-[auto_1fr_auto] items-center gap-2">
                <Button
                    type="button"
                    variant="outline"
                    size="icon-sm"
                    class="justify-self-start"
                    :aria-label="props.prevMonthAriaLabel"
                    @click="visitMonth(-1)"
                >
                    <ChevronLeft
                        class="size-4"
                        aria-hidden="true"
                    />
                </Button>
                <CardTitle class="truncate text-center text-base font-semibold">
                    {{ monthTitle }}
                </CardTitle>
                <Button
                    type="button"
                    variant="outline"
                    size="icon-sm"
                    class="justify-self-end"
                    :aria-label="props.nextMonthAriaLabel"
                    @click="visitMonth(1)"
                >
                    <ChevronRight
                        class="size-4"
                        aria-hidden="true"
                    />
                </Button>
            </div>
        </CardHeader>
        <CardContent class="space-y-4 pb-6 pt-0">
            <div class="min-w-0 rounded-xl border border-border bg-bg px-1 py-2 sm:px-3 sm:py-3">
                <table class="w-full table-fixed border-collapse">
                    <caption class="sr-only">
                        {{ gridCaption }}
                    </caption>
                    <thead>
                        <tr>
                            <th
                                v-for="label in weekdayLabels"
                                :key="label"
                                scope="col"
                                class="truncate px-0.5 py-1 text-center text-2xs font-semibold uppercase tracking-wide text-text-muted sm:text-xs"
                            >
                                {{ label }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(week, weekIndex) in calendarWeeks"
                            :key="weekIndex"
                        >
                            <td
                                v-for="(cell, cellIndex) in week"
                                :key="`${weekIndex}-${cellIndex}`"
                                class="p-0.5 align-top sm:p-1"
                                :aria-hidden="cell.dayNum === null ? true : undefined"
                            >
                                <button
                                    v-if="cell.dayNum !== null && cell.dateKey !== null"
                                    type="button"
                                    :class="dayButtonClass(cell)"
                                    :aria-label="dayButtonAriaLabel(cell)"
                                    :aria-pressed="selectedDate === cell.dateKey"
                                    @click="onDayActivate(cell)"
                                >
                                    <span class="text-2xs font-semibold leading-none text-text-heading sm:text-xs">
                                        {{ cell.dayNum }}
                                    </span>
                                    <span class="mt-auto flex flex-1 flex-col items-center justify-center pb-0.5 pt-1">
                                        <slot
                                            name="day-indicator"
                                            :cell="cell"
                                        />
                                    </span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="$slots.legend"
                class="flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-border pt-4 text-xs text-text-muted"
                aria-hidden="true"
            >
                <slot name="legend" />
            </div>
        </CardContent>
    </Card>
</template>
