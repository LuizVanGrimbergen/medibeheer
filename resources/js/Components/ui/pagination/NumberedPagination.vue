<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import type { PaginationMeta } from '@/lib/types';

const props = defineProps<{
    routeName: string;
    meta: PaginationMeta;
    query?: Record<string, string | number | undefined | null>;
}>();

const { t } = useI18n();

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

const pageItems = computed((): (number | 'ellipsis')[] => {
    const last = props.meta.last_page;
    const current = props.meta.current_page;

    if (last <= 1) {
        return [];
    }

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

        <div class="flex min-w-0 items-center gap-2">
            <Button
                type="button"
                variant="outline"
                size="lg"
                class="shrink-0 touch-manipulation gap-2 px-3 text-sm font-semibold sm:px-5 sm:text-base [&_svg]:size-5 sm:[&_svg]:size-6"
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
                class="flex min-w-0 flex-1 list-none flex-nowrap items-center gap-2 overflow-x-auto overscroll-x-contain p-0 [-webkit-overflow-scrolling:touch] sm:flex-wrap sm:justify-center sm:overflow-visible sm:py-0"
            >
                <template
                    v-for="(item, index) in pageItems"
                    :key="item === 'ellipsis' ? `e-${index}` : `p-${item}`"
                >
                    <li
                        v-if="item === 'ellipsis'"
                        class="flex shrink-0 items-center px-0.5 text-sm font-semibold text-text-muted"
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
                            class="min-h-11 min-w-11 touch-manipulation px-0 text-sm font-semibold sm:min-h-12 sm:min-w-12 sm:text-base"
                            :aria-label="
                                t('app.pagination.goToPage', { page: item })
                            "
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
                class="shrink-0 touch-manipulation gap-2 px-3 text-sm font-semibold sm:px-5 sm:text-base [&_svg]:size-5 sm:[&_svg]:size-6"
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
