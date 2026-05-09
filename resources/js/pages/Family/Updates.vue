<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import ActivePatientBadge from '@/Components/Family/ActivePatientBadge.vue';
import FamilyWellbeingCheckinCard from '@/Components/Family/Wellbeing/FamilyWellbeingCheckinCard.vue';
import FamilyWellbeingMonthCalendar from '@/Components/Family/Wellbeing/FamilyWellbeingMonthCalendar.vue';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import type { FamilyUpdatesScreenProps } from '@/lib/family/wellbeing/familyUpdatesScreenProps';
import type { DailyCheckin, FamilyDashboardProps } from '@/lib/types';

const props = defineProps<
    FamilyUpdatesScreenProps & {
        family: FamilyDashboardProps;
    }
>();

const { t } = useI18n();
const page = usePage();

function pathOnly(urlOrPath: string): string {
    const raw = urlOrPath.startsWith('http')
        ? new URL(urlOrPath).pathname
        : (urlOrPath.split('?')[0] ?? '');

    if (raw.length > 1 && raw.endsWith('/')) {
        return raw.slice(0, -1);
    }

    return raw;
}

const wellbeingPaginationRouteName = computed((): 'family.updates' | 'family.wellbeing' => {
    const pathname = pathOnly(page.url);

    if (pathname === pathOnly(route('family.wellbeing') as string)) {
        return 'family.wellbeing';
    }

    return 'family.updates';
});

const isFamilyWellbeingPage = computed(
    () => pathOnly(page.url) === pathOnly(route('family.wellbeing') as string),
);

const listPaginationQuery = computed((): Record<string, string> | undefined => {
    if (!isFamilyWellbeingPage.value) {
        return undefined;
    }

    return {
        calendar_month: props.wellbeing_calendar_month,
    };
});

const selectedCalendarDate = ref<string | null>(null);
const selectedDaySectionRef = ref<HTMLElement | null>(null);

watch(
    () => props.wellbeing_calendar_month,
    () => {
        selectedCalendarDate.value = null;
    },
);

const checkinsByCalendarDate = computed((): Map<string, DailyCheckin> => {
    const map = new Map<string, DailyCheckin>();

    for (const checkin of props.wellbeing_calendar_checkins) {
        map.set(checkin.checkin_date, checkin);
    }

    return map;
});

const selectedDayCheckin = computed((): DailyCheckin | undefined => {
    const date = selectedCalendarDate.value;

    if (date === null) {
        return undefined;
    }

    return checkinsByCalendarDate.value.get(date);
});

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
</script>

<template>
    <Head>
        <title>{{ t('family.updates.title') }}</title>
    </Head>

    <FamilyLayout>
        <div class="flex min-w-0 flex-col gap-6">
            <div class="space-y-2">
                <h1 class="text-2xl font-semibold text-text-heading">
                    {{ t('family.updates.heading') }}
                </h1>
                <ActivePatientBadge :family="props.family" />
            </div>

            <p
                v-if="!props.family.has_linked_patient"
                class="max-w-prose text-sm leading-relaxed text-text-muted"
            >
                {{ t('family.updates.notLinked') }}
            </p>

            <div
                v-else
                class="flex min-w-0 flex-col gap-6"
            >
                <FamilyWellbeingMonthCalendar
                    v-if="isFamilyWellbeingPage"
                    :calendar-month="props.wellbeing_calendar_month"
                    :calendar-checkins="props.wellbeing_calendar_checkins"
                    :list-route-name="wellbeingPaginationRouteName"
                    :selected-date="selectedCalendarDate"
                    @select-date="onSelectCalendarDate"
                />

                <section
                    v-if="isFamilyWellbeingPage && selectedCalendarDate !== null"
                    ref="selectedDaySectionRef"
                    class="scroll-mt-24 space-y-3"
                    tabindex="-1"
                >
                    <h2 class="text-lg font-semibold text-text-heading">
                        {{ t('family.updates.wellbeing.selectedDayHeading') }}
                    </h2>
                    <FamilyWellbeingCheckinCard
                        v-if="selectedDayCheckin !== undefined"
                        :checkin="selectedDayCheckin"
                    />
                    <p
                        v-else
                        class="text-sm leading-relaxed text-text-muted"
                    >
                        {{ t('family.updates.wellbeing.selectedDayNoCheckin') }}
                    </p>
                </section>

                <template v-if="props.wellbeing_checkins.meta.total === 0">
                    <p class="text-sm leading-relaxed text-text-muted">
                        {{ t('family.updates.wellbeing.empty') }}
                    </p>
                </template>

                <div
                    v-else
                    class="space-y-4"
                >
                    <h2 class="text-lg font-semibold text-text-heading">
                        {{ t('family.updates.wellbeing.listHeading') }}
                    </h2>
                    <FamilyWellbeingCheckinCard
                        v-for="checkin in props.wellbeing_checkins.data"
                        :key="checkin.id"
                        :checkin="checkin"
                    />

                    <NumberedPagination
                        v-if="props.wellbeing_checkins.meta.last_page > 1"
                        :route-name="wellbeingPaginationRouteName"
                        :meta="props.wellbeing_checkins.meta"
                        :query="listPaginationQuery"
                    />
                </div>
            </div>
        </div>
    </FamilyLayout>
</template>
