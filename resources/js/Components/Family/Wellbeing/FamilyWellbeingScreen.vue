<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import ActivePatientBadge from '@/Components/Family/ActivePatientBadge.vue';
import FamilyWellbeingCheckinCard from '@/Components/Family/Wellbeing/FamilyWellbeingCheckinCard.vue';
import FamilyWellbeingMonthCalendar from '@/Components/Family/Wellbeing/FamilyWellbeingMonthCalendar.vue';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import type { FamilyWellbeingScreenProps } from '@/lib/family/wellbeing/familyWellbeingScreenProps';
import type { DailyCheckin, FamilyDashboardProps } from '@/lib/types';

const props = defineProps<
    FamilyWellbeingScreenProps & {
        family: FamilyDashboardProps;
    }
>();

const { t } = useI18n();

const listPaginationQuery = computed((): Record<string, string> => ({
    calendar_month: props.wellbeing_calendar_month,
}));

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
    <div class="flex min-w-0 flex-col gap-6">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-text-heading">
                {{ t('family.wellbeing.heading') }}
            </h1>
            <ActivePatientBadge :family="props.family" />
        </div>

        <p
            v-if="!props.family.has_linked_patient"
            class="max-w-prose text-sm leading-relaxed text-text-muted"
        >
            {{ t('family.wellbeing.notLinked') }}
        </p>

        <div
            v-else
            class="flex min-w-0 flex-col gap-6"
        >
            <FamilyWellbeingMonthCalendar
                :calendar-month="props.wellbeing_calendar_month"
                :calendar-checkins="props.wellbeing_calendar_checkins"
                :selected-date="selectedCalendarDate"
                @select-date="onSelectCalendarDate"
            />

            <section
                v-if="selectedCalendarDate !== null"
                ref="selectedDaySectionRef"
                class="scroll-mt-24 space-y-3"
                tabindex="-1"
            >
                <h2 class="text-lg font-semibold text-text-heading">
                    {{ t('family.wellbeing.selectedDayHeading') }}
                </h2>
                <FamilyWellbeingCheckinCard
                    v-if="selectedDayCheckin !== undefined"
                    :checkin="selectedDayCheckin"
                />
                <p
                    v-else
                    class="text-sm leading-relaxed text-text-muted"
                >
                    {{ t('family.wellbeing.selectedDayNoCheckin') }}
                </p>
            </section>

            <template v-if="props.wellbeing_checkins.meta.total === 0">
                <p class="text-sm leading-relaxed text-text-muted">
                    {{ t('family.wellbeing.empty') }}
                </p>
            </template>

            <div
                v-else
                class="space-y-4"
            >
                <h2 class="text-lg font-semibold text-text-heading">
                    {{ t('family.wellbeing.listHeading') }}
                </h2>
                <FamilyWellbeingCheckinCard
                    v-for="checkin in props.wellbeing_checkins.data"
                    :key="checkin.id"
                    :checkin="checkin"
                />

                <NumberedPagination
                    v-if="props.wellbeing_checkins.meta.last_page > 1"
                    route-name="family.wellbeing"
                    :meta="props.wellbeing_checkins.meta"
                    :query="listPaginationQuery"
                />
            </div>
        </div>
    </div>
</template>
