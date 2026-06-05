<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import HistorySelectedDaySection from '@/Components/History/HistorySelectedDaySection.vue';
import MedicationIntakeHistorySlotCard from '@/Components/Patient/Medications/MedicationIntakeHistorySlotCard.vue';
import MedicationIntakeMonthCalendar from '@/Components/Patient/Medications/MedicationIntakeMonthCalendar.vue';
import type {
    MedicationIntakeCalendarDay,
    MedicationIntakeHistorySlot,
} from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';
import { compareTodayMedicationIntakeSlots } from '@/lib/patient/medications/todayMedicationIntakeDayPeriod';

const props = withDefaults(
    defineProps<{
        calendarMonth: string;
        calendarDays: MedicationIntakeCalendarDay[];
        calendarSlots: MedicationIntakeHistorySlot[];
        navigateRouteName: string;
        navigateQueryKey?: string;
        selectedDayHeadingKey: string;
        selectedDayNoScheduleKey: string;
        selectedDayNoIntakesKey: string;
        slotCardDensity?: 'default' | 'compact';
    }>(),
    {
        navigateQueryKey: 'calendar_month',
        slotCardDensity: 'default',
    },
);

const { t } = useI18n();

const selectedCalendarDate = defineModel<string | null>('selectedDate', {
    default: null,
});

const selectedDaySectionRef = ref<InstanceType<
    typeof HistorySelectedDaySection
> | null>(null);

watch(
    () => props.calendarMonth,
    () => {
        selectedCalendarDate.value = null;
    },
);

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

const slotsByDate = computed((): Map<string, MedicationIntakeHistorySlot[]> => {
    const map = new Map<string, MedicationIntakeHistorySlot[]>();

    for (const slot of props.calendarSlots) {
        const existing = map.get(slot.intake_date) ?? [];

        existing.push(slot);
        map.set(slot.intake_date, existing);
    }

    for (const [date, slots] of map.entries()) {
        map.set(date, [...slots].sort(compareTodayMedicationIntakeSlots));
    }

    return map;
});

const selectedDaySlots = computed((): MedicationIntakeHistorySlot[] => {
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

    return props.calendarDays.some(
        (day) => day.date === date && day.status !== 'no_schedule',
    );
});
</script>

<template>
    <div class="flex min-w-0 flex-col gap-6">
        <MedicationIntakeMonthCalendar
            :calendar-month="props.calendarMonth"
            :calendar-days="props.calendarDays"
            :selected-date="selectedCalendarDate"
            :navigate-route-name="props.navigateRouteName"
            :navigate-query-key="props.navigateQueryKey"
            @select-date="onSelectCalendarDate"
        />

        <HistorySelectedDaySection
            ref="selectedDaySectionRef"
            :selected-date="selectedCalendarDate"
            :heading="t(props.selectedDayHeadingKey)"
        >
            <p
                v-if="!selectedDayHasSchedule"
                class="text-text-muted text-sm leading-relaxed"
            >
                {{ t(props.selectedDayNoScheduleKey) }}
            </p>

            <p
                v-else-if="selectedDaySlots.length === 0"
                class="text-text-muted text-sm leading-relaxed"
            >
                {{ t(props.selectedDayNoIntakesKey) }}
            </p>

            <slot
                v-else
                name="selected-day"
                :selected-date="selectedCalendarDate"
                :slots="selectedDaySlots"
            >
                <div class="flex flex-col gap-4">
                    <MedicationIntakeHistorySlotCard
                        v-for="slot in selectedDaySlots"
                        :key="`${slot.medication_schedule_id}-${slot.dose_time}`"
                        :intake-slot="slot"
                        :density="props.slotCardDensity"
                    />
                </div>
            </slot>
        </HistorySelectedDaySection>
    </div>
</template>
