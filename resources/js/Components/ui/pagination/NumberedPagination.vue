<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import { useTailwindBreakpoints } from '@/composables/ui/useTailwindBreakpoints';
import type { PaginationMeta } from '@/lib/types';

const props = defineProps<{
    routeName: string;
    meta: PaginationMeta;
    query?: Record<string, string | number | undefined | null>;
}>();

const { t } = useI18n();
const { smAndUp } = useTailwindBreakpoints();

const extraQuery = computed((): Record<string, string | number> => {
    const out: Record<string, string | number> = {};
    const raw = props.query ?? {};

    for (const [key, value] of Object.entries(raw)) {
        if (value === undefined || value === null) {
            continue;
        }

        out[key] = value;
    }

    return out;
});

function desktopPageItems(last: number, current: number): (number | 'ellipsis')[] {
    if (last <= 7) {
        return Array.from({ length: last }, (_, index) => index + 1);
    }

    const set = new Set([1, last, current, current - 1, current + 1]);
    const sorted = [...set].filter((p) => p >= 1 && p <= last).sort((a, b) => a - b);
    const out: (number | 'ellipsis')[] = [];
    let previous = 0;

    for (const page of sorted) {
        if (previous > 0 && page - previous > 1) {
            out.push('ellipsis');
        }

        out.push(page);
        previous = page;
    }

    return out;
}

function mobilePageItems(last: number, current: number): number[] {
    if (last <= 3) {
        return Array.from({ length: last }, (_, index) => index + 1);
    }

    let start = current - 1;
    let end = current + 1;

    if (start < 1) {
        start = 1;
        end = 3;
    }

    if (end > last) {
        end = last;
        start = last - 2;
    }

    return [start, start + 1, end];
}

const pageItems = computed((): (number | 'ellipsis')[] => {
    const last = props.meta.last_page;
    const current = props.meta.current_page;

    if (last <= 1) {
        return [];
    }

    if (smAndUp.value) {
        return desktopPageItems(last, current);
    }

    return mobilePageItems(last, current);
});

function visitPage(page: number): void {
    router.get(
        route(props.routeName, { ...extraQuery.value, page }),
        {},
        { preserveState: true, preserveScroll: true },
    );
}
</script>

<template>
    <nav
        class="flex flex-col gap-4 border-t border-border/70 pt-5 sm:flex-row sm:items-center sm:justify-between sm:gap-4"
        :aria-label="t('app.pagination.navLabel')"
    >
        <p class="text-center text-sm text-text-muted sm:text-left">
            {{
                t('app.pagination.page', {
                    current: meta.current_page,
                    last: meta.last_page,
                })
            }}
            <span class="whitespace-nowrap text-text-muted">
                ({{ meta.total }})
            </span>
        </p>

        <div class="flex min-w-0 items-center gap-1 sm:gap-2">
            <Button
                type="button"
                variant="outline"
                size="lg"
                class="shrink-0 touch-manipulation gap-1.5 px-2.5 text-sm font-semibold sm:gap-2 sm:px-5 sm:text-base [&_svg]:size-4.5 sm:[&_svg]:size-6"
                :disabled="meta.current_page <= 1"
                @click="visitPage(meta.current_page - 1)"
            >
                <ChevronLeft
                    class="shrink-0"
                    aria-hidden="true"
                />
                {{ t('app.pagination.previous') }}
            </Button>

            <ul
                class="flex min-w-0 flex-1 list-none flex-nowrap items-center justify-center gap-1 p-0 sm:gap-2"
            >
                <template
                    v-for="(item, index) in pageItems"
                    :key="item === 'ellipsis' ? `e-${index}` : `p-${item}`"
                >
                    <li
                        v-if="item === 'ellipsis'"
                        class="flex shrink-0 items-center px-0 text-xs font-semibold text-text-muted sm:px-0.5 sm:text-sm"
                        aria-hidden="true"
                    >
                        …
                    </li>
                    <li
                        v-else
                        class="shrink-0"
                    >
                        <Button
                            type="button"
                            :variant="item === meta.current_page ? 'default' : 'outline'"
                            size="lg"
                            class="min-h-9 min-w-9 touch-manipulation px-0 text-xs font-semibold sm:min-h-12 sm:min-w-12 sm:text-base"
                            :aria-label="t('app.pagination.goToPage', { page: item })"
                            :aria-current="item === meta.current_page ? 'page' : undefined"
                            @click="visitPage(item)"
                        >
                            {{ item }}
                        </Button>
                    </li>
                </template>
            </ul>

            <Button
                type="button"
                variant="outline"
                size="lg"
                class="shrink-0 touch-manipulation gap-1.5 px-2.5 text-sm font-semibold sm:gap-2 sm:px-5 sm:text-base [&_svg]:size-4.5 sm:[&_svg]:size-6"
                :disabled="meta.current_page >= meta.last_page"
                @click="visitPage(meta.current_page + 1)"
            >
                {{ t('app.pagination.next') }}
                <ChevronRight
                    class="shrink-0"
                    aria-hidden="true"
                />
            </Button>
        </div>
    </nav>
</template>
