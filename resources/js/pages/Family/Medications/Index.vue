<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyPageShell from '@/Components/Family/FamilyPageShell.vue';
import FamilyMedicationIntakeSlotCollapsibleCard from '@/Components/Family/Medications/FamilyMedicationIntakeSlotCollapsibleCard.vue';
import FamilyMedicationRegisterCard from '@/Components/Family/Medications/FamilyMedicationRegisterCard.vue';
import HistorySelectedDaySection from '@/Components/History/HistorySelectedDaySection.vue';
import MedicationIntakeMonthCalendar from '@/Components/Patient/Medications/MedicationIntakeMonthCalendar.vue';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import ListCardSkeleton from '@/Components/ui/skeleton/ListCardSkeleton.vue';
import { useHistorySelectedDay } from '@/composables/history/useHistorySelectedDay';
import { useHistorySelectedDayNavigation } from '@/composables/history/useHistorySelectedDayNavigation';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import type { FamilyMedicationIntakeCalendarSlot } from '@/lib/family/medications/familyMedicationIntakeCalendarSlot';
import type { FamilyMedicationsScreenProps } from '@/lib/family/medications/familyMedicationsScreenProps';
import { readNumericScreenQueryParam } from '@/lib/inertia/readNumericScreenQueryParam';
import { areAnyDeferredInertiaPropsLoading } from '@/lib/inertia/isDeferredInertiaPropLoading';
import { compareMedicationInventoryListItems } from '@/lib/patient/inventory/medicationInventoryListSortRank';
import { compareTodayMedicationIntakeSlots } from '@/lib/patient/medications/todayMedicationIntakeDayPeriod';
import type { FamilyDashboardProps, MedicationRegisterItem, PageProps } from '@/lib/types';

type PageWithFamily = PageProps & { family?: FamilyDashboardProps };

const props = defineProps<Omit<FamilyMedicationsScreenProps, 'family'>>();

const { t } = useI18n();
const page = usePage<PageWithFamily>();

const family = computed(() => page.props.family);

const isMedicationsLoading = computed(() =>
    areAnyDeferredInertiaPropsLoading(
        props.medications,
        props.medication_calendar_days,
        props.medication_calendar_slots,
    ),
);

const {
    selectedCalendarDate,
    selectedDaySectionRef,
    onSelectCalendarDate,
    selectCalendarDate,
} = useHistorySelectedDay(() => props.medication_calendar_month);

const calendarNavigationQuery = computed((): Record<string, string> => {
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

const { selectedDateLabel, showPreviousDay, showNextDay } =
    useHistorySelectedDayNavigation({
        calendarMonth: () => props.medication_calendar_month,
        selectedDay: {
            selectedCalendarDate,
            selectCalendarDate,
        },
        onNavigateMonth(month, date) {
            router.get(
                route('family.medications'),
                {
                    ...calendarNavigationQuery.value,
                    calendar_month: month,
                },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        selectCalendarDate(date);
                    },
                },
            );
        },
    });

const deepLinkedMedicationId = computed((): number | null => {
    const medication = readNumericScreenQueryParam('medication', page.url);

    if (medication === null) {
        return null;
    }

    const id = Number(medication);

    return Number.isNaN(id) ? null : id;
});

const paginationQuery = computed((): Record<string, string | number> => {
    const query: Record<string, string | number> = {
        calendar_month: props.medication_calendar_month,
    };

    const medication = readNumericScreenQueryParam('medication', page.url);

    if (medication !== null) {
        query.medication = medication;
    }

    return query;
});

function scrollToDeepLinkedMedication(): void {
    const medicationId = deepLinkedMedicationId.value;

    if (medicationId === null) {
        return;
    }

    if (
        !props.medications?.data.some(
            (medication) => medication.id === medicationId,
        )
    ) {
        return;
    }

    nextTick(() => {
        document
            .getElementById(`family-medication-${medicationId}`)
            ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
}

watch(
    () => [page.url, props.medications?.data] as const,
    () => {
        scrollToDeepLinkedMedication();
    },
    { immediate: true, deep: true },
);

const slotsByDate = computed(
    (): Map<string, FamilyMedicationIntakeCalendarSlot[]> => {
        const map = new Map<string, FamilyMedicationIntakeCalendarSlot[]>();

        for (const slot of props.medication_calendar_slots ?? []) {
            const existing = map.get(slot.intake_date) ?? [];

            existing.push(slot);
            map.set(slot.intake_date, existing);
        }

        for (const [date, slots] of map.entries()) {
            map.set(date, [...slots].sort(compareTodayMedicationIntakeSlots));
        }

        return map;
    },
);

const selectedDaySlots = computed((): FamilyMedicationIntakeCalendarSlot[] => {
    const date = selectedCalendarDate.value;

    if (date === null) {
        return [];
    }

    return slotsByDate.value.get(date) ?? [];
});

const selectedDayHasSchedule = computed((): boolean => {
    const date = selectedCalendarDate.value;

    if (date === null) {
        return false;
    }

    return (props.medication_calendar_days ?? []).some(
        (day) => day.date === date && day.status !== 'no_schedule',
    );
});

const medicationRegisterListStatusRank = (
    medication: MedicationRegisterItem,
): number => {
    if (medication.list_status === 'active') {
        return 0;
    }

    if (medication.list_status === 'ended') {
        return 1;
    }

    return 2;
};

const sortedRegisterMedications = computed((): MedicationRegisterItem[] => {
    if (props.medications === undefined) {
        return [];
    }

    const items = [...props.medications.data];

    items.sort((left, right) => {
        const statusRank =
            medicationRegisterListStatusRank(left) -
            medicationRegisterListStatusRank(right);

        if (statusRank !== 0) {
            return statusRank;
        }

        return compareMedicationInventoryListItems(left, right);
    });

    return items;
});
</script>

<template>
    <Head>
        <title>{{ t('family.medications.title') }}</title>
    </Head>

    <FamilyLayout>
        <FamilyPageShell
            :title="t('family.medications.heading')"
            :family="family"
            :show-active-patient="family?.has_linked_patient ?? false"
        >
            <p
                v-if="!family?.has_linked_patient"
                class="text-text-muted max-w-prose text-sm leading-relaxed"
            >
                {{ t('family.medications.notLinked') }}
            </p>

            <ListCardSkeleton v-else-if="isMedicationsLoading" />

            <div v-else class="flex min-w-0 flex-col gap-6">
                <MedicationIntakeMonthCalendar
                    :calendar-month="props.medication_calendar_month"
                    :calendar-days="props.medication_calendar_days ?? []"
                    :selected-date="selectedCalendarDate"
                    navigate-route-name="family.medications"
                    @select-date="onSelectCalendarDate"
                />

                <HistorySelectedDaySection
                    ref="selectedDaySectionRef"
                    :selected-date="selectedCalendarDate"
                    :heading="t('family.medications.selectedDayHeading')"
                    show-day-navigation
                    :selected-date-label="selectedDateLabel"
                    :prev-day-aria-label="
                        t('patient.medications.history.calendar.prevDay')
                    "
                    :next-day-aria-label="
                        t('patient.medications.history.calendar.nextDay')
                    "
                    @previous-day="showPreviousDay"
                    @next-day="showNextDay"
                >
                    <p
                        v-if="!selectedDayHasSchedule"
                        class="text-text-muted text-sm leading-relaxed"
                    >
                        {{
                            t(
                                'patient.medications.history.selectedDayNoSchedule',
                            )
                        }}
                    </p>

                    <p
                        v-else-if="selectedDaySlots.length === 0"
                        class="text-text-muted text-sm leading-relaxed"
                    >
                        {{
                            t(
                                'patient.medications.history.selectedDayNoIntakes',
                            )
                        }}
                    </p>

                    <div v-else class="space-y-4">
                        <FamilyMedicationIntakeSlotCollapsibleCard
                            v-for="slot in selectedDaySlots"
                            :key="`${slot.medication_schedule_id}-${slot.dose_time}`"
                            :intake-slot="slot"
                        />
                    </div>
                </HistorySelectedDaySection>

                <template v-if="selectedCalendarDate === null">
                    <p
                        v-if="(props.medications?.meta.total ?? 0) === 0"
                        class="text-text-muted text-sm leading-relaxed"
                    >
                        {{ t('family.medications.empty') }}
                    </p>

                    <div v-else class="space-y-4">
                        <h2
                            class="text-text-heading text-lg font-semibold md:text-base"
                        >
                            {{ t('family.medications.listHeading') }}
                        </h2>

                        <FamilyMedicationRegisterCard
                            v-for="medication in sortedRegisterMedications"
                            :id="`family-medication-${medication.id}`"
                            :key="medication.id"
                            class="block min-w-0 scroll-mt-6"
                            :medication="medication"
                            :default-open="
                                medication.id === deepLinkedMedicationId
                            "
                        />
                    </div>

                    <NumberedPagination
                        v-if="
                            selectedCalendarDate === null &&
                            props.medications !== undefined &&
                            props.medications.meta.last_page > 1
                        "
                        route-name="family.medications"
                        :meta="props.medications.meta"
                        :query="paginationQuery"
                    />
                </template>
            </div>
        </FamilyPageShell>
    </FamilyLayout>
</template>
