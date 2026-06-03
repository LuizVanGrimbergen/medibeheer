<script setup lang="ts">
import { computed, toRef } from 'vue';
import { useI18n } from 'vue-i18n';
import HistoryMonthNavigation from '@/Components/History/HistoryMonthNavigation.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { useHistoryMonthCalendarGrid } from '@/composables/history/useHistoryMonthCalendarGrid';
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
        density?: 'default' | 'compact';
        showMonthNavigation?: boolean;
        headerTitle?: string;
    }>(),
    {
        selectedDate: null,
        navigateQueryKey: 'calendar_month',
        density: 'default',
        showMonthNavigation: true,
        headerTitle: undefined,
    },
);

const emit = defineEmits<{
    selectDate: [dateKey: string];
}>();

const { t } = useI18n();

const calendarMonthRef = toRef(props, 'calendarMonth');

const { monthTitle, weekdayLabels, calendarWeeks } =
    useHistoryMonthCalendarGrid(calendarMonthRef);

const gridCaption = computed(() =>
    t(props.gridCaptionKey, { month: monthTitle.value }),
);

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
        'flex w-full max-w-full min-w-0 flex-col items-center justify-start rounded-lg border px-0.5 py-0.5 text-center transition-colors',
        props.density === 'compact'
            ? 'min-h-10 sm:min-h-11'
            : 'min-h-14 py-1 sm:min-h-16',
        'border-transparent bg-surface-hover/40 hover:bg-surface-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary',
        selected && 'ring-2 ring-inset ring-primary/40',
    );
}

function dayButtonAriaLabel(cell: HistoryMonthCalendarCell): string {
    return `${props.dayAriaLabel(cell)}. ${props.openDayDetailsAriaLabel}`;
}
</script>

<template>
    <Card class="border-border min-w-0">
        <CardHeader
            v-if="props.showMonthNavigation || props.headerTitle !== undefined"
            :class="props.density === 'compact' ? 'pt-4 pb-2' : 'pt-6 pb-4'"
        >
            <HistoryMonthNavigation
                v-if="props.showMonthNavigation"
                :calendar-month="props.calendarMonth"
                :navigate-route-name="props.navigateRouteName"
                :navigate-query-key="props.navigateQueryKey"
                :prev-month-aria-label="props.prevMonthAriaLabel"
                :next-month-aria-label="props.nextMonthAriaLabel"
                :density="props.density"
            />
            <CardTitle
                v-else
                :class="
                    cn(
                        'font-semibold',
                        props.density === 'compact' ? 'text-sm' : 'text-base',
                    )
                "
            >
                {{ props.headerTitle }}
            </CardTitle>
        </CardHeader>
        <CardContent
            :class="
                cn(
                    'pt-0',
                    props.density === 'compact'
                        ? 'space-y-3 pb-4'
                        : 'space-y-4 pb-6',
                )
            "
        >
            <div
                :class="
                    cn(
                        'border-border bg-bg min-w-0 rounded-xl border',
                        props.density === 'compact'
                            ? 'px-1 py-1.5'
                            : 'px-1 py-2 sm:px-3 sm:py-3',
                    )
                "
            >
                <table class="w-full table-fixed border-collapse">
                    <caption class="sr-only">
                        {{
                            gridCaption
                        }}
                    </caption>
                    <thead>
                        <tr>
                            <th
                                v-for="label in weekdayLabels"
                                :key="label"
                                scope="col"
                                class="text-2xs text-text-muted truncate px-0.5 py-1 text-center font-semibold tracking-wide uppercase sm:text-xs"
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
                                class="p-0.5 align-top"
                                :aria-hidden="
                                    cell.dayNum === null ? true : undefined
                                "
                            >
                                <button
                                    v-if="
                                        cell.dayNum !== null &&
                                        cell.dateKey !== null
                                    "
                                    type="button"
                                    :class="dayButtonClass(cell)"
                                    :aria-label="dayButtonAriaLabel(cell)"
                                    :aria-pressed="
                                        selectedDate === cell.dateKey
                                    "
                                    @click="onDayActivate(cell)"
                                >
                                    <span
                                        :class="
                                            cn(
                                                'text-text-heading leading-none font-semibold',
                                                props.density === 'compact'
                                                    ? 'text-2xs'
                                                    : 'text-2xs sm:text-xs',
                                            )
                                        "
                                    >
                                        {{ cell.dayNum }}
                                    </span>
                                    <span
                                        class="mt-auto flex flex-1 flex-col items-center justify-center pt-1 pb-0.5"
                                    >
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
                :class="
                    cn(
                        'border-border text-text-muted flex flex-wrap items-center border-t',
                        props.density === 'compact'
                            ? 'text-2xs gap-x-3 gap-y-1.5 pt-3'
                            : 'gap-x-4 gap-y-2 pt-4 text-xs',
                    )
                "
                aria-hidden="true"
            >
                <slot name="legend" />
            </div>
        </CardContent>
    </Card>
</template>
