<script setup lang="ts">
import { Heart } from 'lucide-vue-next';
import { computed, ref, toRef, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import DoctorCollapsibleSection from '@/Components/Doctor/Patients/DoctorCollapsibleSection.vue';
import FamilyWellbeingCheckinCard from '@/Components/Family/Wellbeing/FamilyWellbeingCheckinCard.vue';
import FamilyWellbeingMonthCalendar from '@/Components/Family/Wellbeing/FamilyWellbeingMonthCalendar.vue';
import HistorySelectedDaySection from '@/Components/History/HistorySelectedDaySection.vue';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import { useHistoryMonthCalendarGrid } from '@/composables/history/useHistoryMonthCalendarGrid';
import { useHistorySelectedDay } from '@/composables/history/useHistorySelectedDay';
import { filterDoctorPatientWellbeingCheckins } from '@/lib/doctor/patients/filterDoctorPatientWellbeingCheckins';
import { indexWellbeingCalendarCheckins } from '@/lib/family/wellbeing/indexWellbeingCalendarCheckins';
import { dailyMoodPresentation } from '@/lib/mood/dailyMoodPresentation';
import type { DailyCheckin, DailyMoodScoreValue, Paginated } from '@/lib/types';
import { scrollExpandedSectionIntoView } from '@/lib/ui/scrollExpandedSectionIntoView';

const DOCTOR_WELLBEING_MOOD_ORDER: DailyMoodScoreValue[] = [
    'good',
    'ok',
    'bad',
];

const props = defineProps<{
    wellbeing_calendar_month: string;
    wellbeing_calendar_checkins: DailyCheckin[];
    wellbeing_checkins: Paginated<DailyCheckin>;
}>();

const open = defineModel<boolean>('open', { default: false });
const moodFilter = defineModel<DailyMoodScoreValue | null>('moodFilter', {
    default: null,
});

const { t } = useI18n();

const sectionRef = ref<InstanceType<typeof DoctorCollapsibleSection> | null>(
    null,
);

defineExpose({
    scrollIntoView(): void {
        scrollExpandedSectionIntoView(sectionRef.value?.$el);
    },
});

const preservedDashboardQuery = computed((): Record<string, string> => {
    if (typeof globalThis.window === 'undefined') {
        return {};
    }

    const params = new URLSearchParams(globalThis.window.location.search);
    const query: Record<string, string> = {};

    params.forEach((value, key) => {
        if (key !== 'calendar_month') {
            query[key] = value;
        }
    });

    return query;
});

const listPaginationQuery = computed(
    (): Record<string, string> => ({
        ...preservedDashboardQuery.value,
        calendar_month: props.wellbeing_calendar_month,
    }),
);

const wellbeingCalendarMonthRef = toRef(props, 'wellbeing_calendar_month');
const { monthTitle: wellbeingMonthTitle } = useHistoryMonthCalendarGrid(
    wellbeingCalendarMonthRef,
);

const filteredCalendarCheckins = computed(() =>
    filterDoctorPatientWellbeingCheckins(
        props.wellbeing_calendar_checkins,
        moodFilter.value,
    ),
);

const wellbeingCalendarIndex = computed(() =>
    indexWellbeingCalendarCheckins(props.wellbeing_calendar_checkins),
);

const wellbeingMoodCounts = computed(
    (): Record<DailyMoodScoreValue, number> => {
        const counts: Record<DailyMoodScoreValue, number> = {
            bad: 0,
            ok: 0,
            good: 0,
        };

        for (const checkin of props.wellbeing_calendar_checkins) {
            counts[checkin.mood_score] += 1;
        }

        return counts;
    },
);

function onMoodFilterSelect(mood: DailyMoodScoreValue): void {
    moodFilter.value = mood;
}

const {
    selectedCalendarDate,
    selectedDaySectionRef,
    onSelectCalendarDate,
    selectCalendarDate,
} = useHistorySelectedDay(() => props.wellbeing_calendar_month);

watch(moodFilter, () => {
    selectCalendarDate(null, false);
});

const visibleCheckins = computed((): DailyCheckin[] => {
    if (moodFilter.value === null) {
        return props.wellbeing_checkins.data;
    }

    return [...filteredCalendarCheckins.value].sort((left, right) =>
        right.checkin_date.localeCompare(left.checkin_date),
    );
});

const checkinListHeading = computed((): string => {
    if (moodFilter.value === null) {
        return t('family.wellbeing.listHeading');
    }

    return t('doctor.patients.wellbeingFilteredListHeading', {
        mood: t(dailyMoodPresentation(moodFilter.value).labelKey),
    });
});

const showCheckinPagination = computed(
    (): boolean =>
        moodFilter.value === null &&
        props.wellbeing_checkins.meta.last_page > 1,
);

const selectedDayCheckin = computed(() => {
    const date = selectedCalendarDate.value;

    if (date === null) {
        return undefined;
    }

    return wellbeingCalendarIndex.value.checkinsByDate.get(date);
});

const wellbeingCollapsedSummary = computed((): string => {
    const count = props.wellbeing_calendar_checkins.length;

    if (count === 0) {
        return t('family.wellbeing.empty');
    }

    if (count === 1) {
        return t('doctor.patients.wellbeingCollapsedOne', {
            month: wellbeingMonthTitle.value,
        });
    }

    return t('doctor.patients.wellbeingCollapsedMany', {
        count: String(count),
        month: wellbeingMonthTitle.value,
    });
});
</script>

<template>
    <DoctorCollapsibleSection
        ref="sectionRef"
        v-model:open="open"
        :heading="t('family.wellbeing.calendar.title')"
        :toggle-label="t('doctor.patients.wellbeingToggle')"
        :collapsed-summary="wellbeingCollapsedSummary"
    >
        <template #icon>
            <Heart class="size-5" />
        </template>

        <div class="flex min-w-0 flex-col gap-4">
            <FamilyWellbeingMonthCalendar
                :calendar-month="props.wellbeing_calendar_month"
                :moods-by-date="wellbeingCalendarIndex.moodsByDate"
                :selected-date="selectedCalendarDate"
                :mood-filter="moodFilter"
                :mood-counts="wellbeingMoodCounts"
                :selectable-legend-moods="DOCTOR_WELLBEING_MOOD_ORDER"
                navigate-route-name="doctor.dashboard"
                density="compact"
                @select-date="onSelectCalendarDate"
                @select-mood-filter="onMoodFilterSelect"
            />

            <HistorySelectedDaySection
                ref="selectedDaySectionRef"
                :selected-date="selectedCalendarDate"
                :heading="t('family.wellbeing.selectedDayHeading')"
                density="compact"
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
                    v-if="visibleCheckins.length === 0"
                    class="text-text-muted text-sm leading-relaxed"
                >
                    {{ t('family.wellbeing.empty') }}
                </p>

                <div v-else class="space-y-4">
                    <h2
                        class="text-text-heading text-lg font-semibold md:text-base"
                    >
                        {{ checkinListHeading }}
                    </h2>

                    <FamilyWellbeingCheckinCard
                        v-for="checkin in visibleCheckins"
                        :key="checkin.id"
                        :checkin="checkin"
                    />

                    <NumberedPagination
                        v-if="showCheckinPagination"
                        route-name="doctor.dashboard"
                        density="compact"
                        :meta="props.wellbeing_checkins.meta"
                        :query="listPaginationQuery"
                    />
                </div>
            </template>
        </div>
    </DoctorCollapsibleSection>
</template>
