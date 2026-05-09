<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';
import { ChevronLeft, ChevronRight, Frown, Meh, Smile } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import type { DailyCheckin, DailyMoodScoreValue } from '@/lib/types';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        calendarMonth: string;
        calendarCheckins: DailyCheckin[];
        selectedDate?: string | null;
    }>(),
    { selectedDate: null },
);

const emit = defineEmits<{
    selectDate: [dateKey: string];
}>();

const { t, locale } = useI18n();

const moodByDate = computed(() => {
    const map = new Map<string, DailyMoodScoreValue>();

    for (const checkin of props.calendarCheckins) {
        map.set(checkin.checkin_date, checkin.mood_score);
    }

    return map;
});

const parsedMonth = computed((): { year: number; month: number } => {
    const parts = props.calendarMonth.split('-').map(Number);
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

type GridCell = {
    dateKey: string | null;
    dayNum: number | null;
    mood: DailyMoodScoreValue | null;
};

const calendarWeeks = computed((): GridCell[][] => {
    const { year, month } = parsedMonth.value;
    const lastDay = new Date(year, month, 0).getDate();
    const mondayOffset = (new Date(year, month - 1, 1).getDay() + 6) % 7;

    const cells: GridCell[] = [];

    for (let i = 0; i < mondayOffset; i++) {
        cells.push({
            dateKey: null,
            dayNum: null,
            mood: null,
        });
    }

    const map = moodByDate.value;

    for (let day = 1; day <= lastDay; day++) {
        const dateKey = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

        cells.push({
            dateKey,
            dayNum: day,
            mood: map.get(dateKey) ?? null,
        });
    }

    while (cells.length % 7 !== 0) {
        cells.push({
            dateKey: null,
            dayNum: null,
            mood: null,
        });
    }

    const weeks: GridCell[][] = [];

    for (let i = 0; i < cells.length; i += 7) {
        weeks.push(cells.slice(i, i + 7));
    }

    return weeks;
});

function visitMonth(delta: number): void {
    const { year, month } = parsedMonth.value;
    const next = new Date(year, month - 1 + delta, 1);
    const ym = `${next.getFullYear()}-${String(next.getMonth() + 1).padStart(2, '0')}`;

    router.get(
        route('family.wellbeing'),
        { calendar_month: ym },
        { preserveScroll: true },
    );
}

function moodGlyph(mood: DailyMoodScoreValue): LucideIcon {
    if (mood === 'bad') {
        return Frown;
    }

    if (mood === 'ok') {
        return Meh;
    }

    return Smile;
}

function moodIconClass(mood: DailyMoodScoreValue): string {
    if (mood === 'bad') {
        return 'text-danger';
    }

    if (mood === 'ok') {
        return 'text-warning';
    }

    return 'text-success';
}

function parseLocalDate(iso: string): Date {
    const parts = iso.split('-').map(Number);
    const y = parts[0];
    const m = parts[1];
    const d = parts[2];

    if (y === undefined || m === undefined || d === undefined) {
        return new Date(iso);
    }

    return new Date(y, m - 1, d);
}

function cellAriaLabel(cell: GridCell): string {
    if (cell.dateKey === null || cell.dayNum === null) {
        return '';
    }

    const loc = locale.value === 'nl' ? 'nl-NL' : undefined;
    const longDate = new Intl.DateTimeFormat(loc, {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(parseLocalDate(cell.dateKey));

    if (cell.mood === null) {
        return t('family.wellbeing.calendar.dayNoCheckin', { date: longDate });
    }

    return t('family.wellbeing.calendar.dayWithMood', {
        date: longDate,
        mood: t(`family.wellbeing.mood.${cell.mood}`),
    });
}

function onDayActivate(cell: GridCell): void {
    if (cell.dateKey === null) {
        return;
    }

    emit('selectDate', cell.dateKey);
}

function dayButtonClass(cell: GridCell): string {
    const selected =
        cell.dateKey !== null && props.selectedDate === cell.dateKey;

    return cn(
        'flex min-h-14 w-full max-w-full min-w-0 flex-col items-center justify-start rounded-lg border px-0.5 py-1 text-center transition-colors sm:min-h-16',
        'border-transparent bg-surface-hover/40 hover:bg-surface-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary',
        selected && 'ring-2 ring-inset ring-primary/40',
    );
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
                    :aria-label="t('family.wellbeing.calendar.prevMonth')"
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
                    :aria-label="t('family.wellbeing.calendar.nextMonth')"
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
                        {{ t('family.wellbeing.calendar.gridAria', { month: monthTitle }) }}
                    </caption>
                    <thead>
                        <tr>
                            <th
                                v-for="label in weekdayLabels"
                                :key="label"
                                scope="col"
                                class="truncate px-0.5 py-1 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted sm:text-xs"
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
                                    :aria-label="`${cellAriaLabel(cell)}. ${t('family.wellbeing.calendar.openDayDetails')}`"
                                    :aria-pressed="selectedDate === cell.dateKey"
                                    @click="onDayActivate(cell)"
                                >
                                    <span class="text-[11px] font-semibold leading-none text-text-heading sm:text-xs">
                                        {{ cell.dayNum }}
                                    </span>
                                    <span class="mt-auto flex flex-1 flex-col items-center justify-center pb-0.5 pt-1">
                                        <component
                                            v-if="cell.mood !== null"
                                            :is="moodGlyph(cell.mood)"
                                            :class="
                                                cn(
                                                    'size-[1.05rem] shrink-0 stroke-[2.25] sm:size-5',
                                                    moodIconClass(cell.mood),
                                                )
                                            "
                                            aria-hidden="true"
                                        />
                                        <span
                                            v-else
                                            class="size-1.5 rounded-full bg-border sm:size-2"
                                            aria-hidden="true"
                                        />
                                    </span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                class="flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-border pt-4 text-xs text-text-muted"
                aria-hidden="true"
            >
                <span class="inline-flex items-center gap-1.5 font-medium text-text-heading">
                    <Frown
                        class="size-4 shrink-0 stroke-2 text-danger"
                        aria-hidden="true"
                    />
                    {{ t('family.wellbeing.mood.bad') }}
                </span>
                <span class="inline-flex items-center gap-1.5 font-medium text-text-heading">
                    <Meh
                        class="size-4 shrink-0 stroke-2 text-warning"
                        aria-hidden="true"
                    />
                    {{ t('family.wellbeing.mood.ok') }}
                </span>
                <span class="inline-flex items-center gap-1.5 font-medium text-text-heading">
                    <Smile
                        class="size-4 shrink-0 stroke-2 text-success"
                        aria-hidden="true"
                    />
                    {{ t('family.wellbeing.mood.good') }}
                </span>
            </div>
        </CardContent>
    </Card>
</template>
