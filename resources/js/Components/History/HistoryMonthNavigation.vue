<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed, toRef } from 'vue';
import { Button } from '@/Components/ui/button';
import { useHistoryMonthCalendarGrid } from '@/composables/history/useHistoryMonthCalendarGrid';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        calendarMonth: string;
        navigateRouteName: string;
        navigateQueryKey?: string;
        prevMonthAriaLabel: string;
        nextMonthAriaLabel: string;
        density?: 'default' | 'compact';
    }>(),
    {
        navigateQueryKey: 'calendar_month',
        density: 'default',
    },
);

const calendarMonthRef = toRef(props, 'calendarMonth');

const { monthTitle, shiftMonth } =
    useHistoryMonthCalendarGrid(calendarMonthRef);

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
</script>

<template>
    <div class="grid w-full grid-cols-[auto_1fr_auto] items-center gap-2">
        <Button
            type="button"
            variant="ghost"
            size="icon-sm"
            class="text-primary hover:bg-surface-hover justify-self-start"
            :aria-label="props.prevMonthAriaLabel"
            @click="visitMonth(-1)"
        >
            <ChevronLeft
                class="size-5 shrink-0 stroke-[2.25]"
                aria-hidden="true"
            />
        </Button>
        <h2
            :class="
                cn(
                    'text-text-heading truncate text-center leading-snug font-semibold',
                    props.density === 'compact'
                        ? 'text-sm'
                        : 'text-base md:text-lg',
                )
            "
        >
            {{ monthTitle }}
        </h2>
        <Button
            type="button"
            variant="ghost"
            size="icon-sm"
            class="text-primary hover:bg-surface-hover justify-self-end"
            :aria-label="props.nextMonthAriaLabel"
            @click="visitMonth(1)"
        >
            <ChevronRight
                class="size-5 shrink-0 stroke-[2.25]"
                aria-hidden="true"
            />
        </Button>
    </div>
</template>
