<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyPageShell from '@/Components/Family/FamilyPageShell.vue';
import FamilyWellbeingCheckinCard from '@/Components/Family/Wellbeing/FamilyWellbeingCheckinCard.vue';
import FamilyWellbeingMonthCalendar from '@/Components/Family/Wellbeing/FamilyWellbeingMonthCalendar.vue';
import HistorySelectedDaySection from '@/Components/History/HistorySelectedDaySection.vue';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import ListCardSkeleton from '@/Components/ui/skeleton/ListCardSkeleton.vue';
import { useHistorySelectedDay } from '@/composables/history/useHistorySelectedDay';
import type { FamilyWellbeingScreenProps } from '@/lib/family/wellbeing/familyWellbeingScreenProps';
import { indexWellbeingCalendarCheckins } from '@/lib/family/wellbeing/indexWellbeingCalendarCheckins';
import { areAnyDeferredInertiaPropsLoading } from '@/lib/inertia/isDeferredInertiaPropLoading';
import type { FamilyDashboardProps } from '@/lib/types';

const props = defineProps<{
    family?: FamilyDashboardProps;
    wellbeing_calendar_month: string;
    wellbeing_calendar_checkins?: FamilyWellbeingScreenProps['wellbeing_calendar_checkins'];
    wellbeing_checkins?: FamilyWellbeingScreenProps['wellbeing_checkins'];
}>();

const { t } = useI18n();

const isWellbeingLoading = computed(() =>
    areAnyDeferredInertiaPropsLoading(
        props.wellbeing_checkins,
        props.wellbeing_calendar_checkins,
    ),
);

const listPaginationQuery = computed(
    (): Record<string, string> => ({
        calendar_month: props.wellbeing_calendar_month,
    }),
);

const wellbeingCalendarIndex = computed(() =>
    indexWellbeingCalendarCheckins(
        props.wellbeing_calendar_checkins ?? [],
    ),
);

const { selectedCalendarDate, selectedDaySectionRef, onSelectCalendarDate } =
    useHistorySelectedDay(() => props.wellbeing_calendar_month);

const selectedDayCheckin = computed(() => {
    const date = selectedCalendarDate.value;

    if (date === null) {
        return undefined;
    }

    return wellbeingCalendarIndex.value.checkinsByDate.get(date);
});

const wellbeingCheckinsList = computed(
    () => props.wellbeing_checkins?.data ?? [],
);

const wellbeingCheckinsMeta = computed(
    () => props.wellbeing_checkins?.meta,
);
</script>

<template>
    <FamilyPageShell
        :title="t('family.wellbeing.heading')"
        :family="props.family"
        :show-active-patient="props.family?.has_linked_patient ?? false"
    >
        <p
            v-if="!props.family?.has_linked_patient"
            class="text-text-muted max-w-prose text-sm leading-relaxed"
        >
            {{ t('family.wellbeing.notLinked') }}
        </p>

        <ListCardSkeleton v-else-if="isWellbeingLoading" />

        <div v-else class="flex min-w-0 flex-col gap-6">
            <FamilyWellbeingMonthCalendar
                :calendar-month="props.wellbeing_calendar_month"
                :moods-by-date="wellbeingCalendarIndex.moodsByDate"
                :selected-date="selectedCalendarDate"
                @select-date="onSelectCalendarDate"
            />

            <HistorySelectedDaySection
                ref="selectedDaySectionRef"
                :selected-date="selectedCalendarDate"
                :heading="t('family.wellbeing.selectedDayHeading')"
            >
                <FamilyWellbeingCheckinCard
                    v-if="selectedDayCheckin !== undefined"
                    :checkin="selectedDayCheckin"
                    density="compact"
                />
                <p v-else class="text-text-muted text-sm leading-relaxed">
                    {{ t('family.wellbeing.selectedDayNoCheckin') }}
                </p>
            </HistorySelectedDaySection>

            <template v-if="selectedCalendarDate === null">
                <p
                    v-if="(props.wellbeing_checkins?.meta.total ?? 0) === 0"
                    class="text-text-muted text-sm leading-relaxed"
                >
                    {{ t('family.wellbeing.empty') }}
                </p>

                <div v-else class="space-y-4">
                    <h2
                        class="text-text-heading text-lg font-semibold md:text-base"
                    >
                        {{ t('family.wellbeing.listHeading') }}
                    </h2>
                    <FamilyWellbeingCheckinCard
                        v-for="checkin in wellbeingCheckinsList"
                        :key="checkin.id"
                        :checkin="checkin"
                    />

                    <NumberedPagination
                        v-if="
                            wellbeingCheckinsMeta !== undefined &&
                            wellbeingCheckinsMeta.last_page > 1
                        "
                        route-name="family.wellbeing"
                        :meta="wellbeingCheckinsMeta"
                        :query="listPaginationQuery"
                    />
                </div>
            </template>
        </div>
    </FamilyPageShell>
</template>
