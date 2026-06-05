<script setup lang="ts">
import { Pill } from 'lucide-vue-next';
import { computed, ref, toRef, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import DoctorCollapsibleSection from '@/Components/Doctor/Patients/DoctorCollapsibleSection.vue';
import MedicationIntakeSlotStatusCard from '@/Components/Medications/MedicationIntakeSlotStatusCard.vue';
import HistorySelectedDaySection from '@/Components/History/HistorySelectedDaySection.vue';
import MedicationIntakeMonthCalendar from '@/Components/Patient/Medications/MedicationIntakeMonthCalendar.vue';
import { useHistoryMonthCalendarGrid } from '@/composables/history/useHistoryMonthCalendarGrid';
import { useHistorySelectedDay } from '@/composables/history/useHistorySelectedDay';
import { filterDoctorPatientMedicationSlots } from '@/lib/doctor/patients/filterDoctorPatientMedicationSlots';
import { medicationIntakeDayPresentation } from '@/lib/patient/medications/history/medicationIntakeDayPresentation';
import type { MedicationIntakeDayIconStatusValue } from '@/lib/patient/medications/history/medicationIntakeDayPresentation';
import type {
    MedicationIntakeCalendarDay,
    MedicationIntakeHistorySlot,
} from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';
import { compareTodayMedicationIntakeSlots } from '@/lib/patient/medications/todayMedicationIntakeDayPeriod';
import { scrollExpandedSectionIntoView } from '@/lib/ui/scrollExpandedSectionIntoView';

const MEDICATION_STATUS_DISPLAY_ORDER: MedicationIntakeDayIconStatusValue[] = [
    'complete',
    'partial',
    'none_taken',
];

const props = defineProps<{
    medication_calendar_month: string;
    medication_calendar_days: MedicationIntakeCalendarDay[];
    medication_calendar_slots: MedicationIntakeHistorySlot[];
    statusCounts: Record<MedicationIntakeDayIconStatusValue, number>;
}>();

const open = defineModel<boolean>('open', { default: false });
const statusFilter = defineModel<MedicationIntakeDayIconStatusValue | null>(
    'statusFilter',
    { default: null },
);

const { t } = useI18n();

const sectionRef = ref<InstanceType<typeof DoctorCollapsibleSection> | null>(
    null,
);

defineExpose({
    scrollIntoView(): void {
        scrollExpandedSectionIntoView(sectionRef.value?.$el);
    },
});

const medicationCalendarMonthRef = toRef(props, 'medication_calendar_month');
const { monthTitle: medicationMonthTitle } = useHistoryMonthCalendarGrid(
    medicationCalendarMonthRef,
);

const {
    selectedCalendarDate,
    selectedDaySectionRef,
    onSelectCalendarDate,
    selectCalendarDate,
} = useHistorySelectedDay(() => props.medication_calendar_month);

watch(statusFilter, () => {
    selectCalendarDate(null, false);
});

const slotsByDate = computed((): Map<string, MedicationIntakeHistorySlot[]> => {
    const map = new Map<string, MedicationIntakeHistorySlot[]>();

    for (const slot of props.medication_calendar_slots) {
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

    return props.medication_calendar_days.some(
        (day) => day.date === date && day.status !== 'no_schedule',
    );
});

const visibleSlots = computed((): MedicationIntakeHistorySlot[] => {
    const filtered = filterDoctorPatientMedicationSlots(
        props.medication_calendar_slots,
        props.medication_calendar_days,
        statusFilter.value,
    );

    return [...filtered].sort((left, right) => {
        const byDate = right.intake_date.localeCompare(left.intake_date);

        if (byDate !== 0) {
            return byDate;
        }

        return compareTodayMedicationIntakeSlots(left, right);
    });
});

const slotListHeading = computed((): string => {
    if (statusFilter.value === null) {
        return t('doctor.patients.medicationListHeading');
    }

    return t('doctor.patients.medicationFilteredListHeading', {
        status: t(medicationIntakeDayPresentation(statusFilter.value).labelKey),
    });
});

const monthAdherenceSummary = computed((): string => {
    const scheduledDays = props.medication_calendar_days.filter(
        (day) => day.status !== 'no_schedule',
    );

    if (scheduledDays.length === 0) {
        return t('doctor.patients.monthAdherenceEmpty');
    }

    const completeDays = scheduledDays.filter(
        (day) => day.status === 'complete',
    ).length;

    return t('doctor.patients.monthAdherence', {
        complete: String(completeDays),
        total: String(scheduledDays.length),
    });
});

const medicationCollapsedSummary = computed(
    (): string =>
        `${medicationMonthTitle.value} · ${monthAdherenceSummary.value}`,
);

function onMedicationStatusFilterSelect(
    status: MedicationIntakeDayIconStatusValue,
): void {
    statusFilter.value = status;
}
</script>

<template>
    <DoctorCollapsibleSection
        ref="sectionRef"
        v-model:open="open"
        :heading="t('patient.medications.history.calendar.title')"
        :toggle-label="t('doctor.patients.medicationToggle')"
        :collapsed-summary="medicationCollapsedSummary"
    >
        <template #icon>
            <Pill class="size-5" />
        </template>

        <div class="flex min-w-0 flex-col gap-4">
            <MedicationIntakeMonthCalendar
                :calendar-month="props.medication_calendar_month"
                :calendar-days="props.medication_calendar_days"
                :selected-date="selectedCalendarDate"
                :status-filter="statusFilter"
                :status-counts="props.statusCounts"
                :selectable-legend-statuses="MEDICATION_STATUS_DISPLAY_ORDER"
                navigate-route-name="doctor.dashboard"
                navigate-query-key="calendar_month"
                density="compact"
                @select-date="onSelectCalendarDate"
                @select-status-filter="onMedicationStatusFilterSelect"
            />

            <HistorySelectedDaySection
                ref="selectedDaySectionRef"
                :selected-date="selectedCalendarDate"
                :heading="t('patient.medications.history.selectedDayHeading')"
                density="compact"
            >
                <p
                    v-if="!selectedDayHasSchedule"
                    class="text-text-muted text-sm leading-relaxed"
                >
                    {{
                        t('patient.medications.history.selectedDayNoSchedule')
                    }}
                </p>

                <p
                    v-else-if="selectedDaySlots.length === 0"
                    class="text-text-muted text-sm leading-relaxed"
                >
                    {{
                        t('patient.medications.history.selectedDayNoIntakes')
                    }}
                </p>

                <div
                    v-else
                    class="space-y-4"
                >
                    <MedicationIntakeSlotStatusCard
                        v-for="slot in selectedDaySlots"
                        :key="`${slot.medication_schedule_id}-${slot.dose_time}`"
                        :intake-slot="slot"
                    />
                </div>
            </HistorySelectedDaySection>

            <template v-if="selectedCalendarDate === null">
                <p
                    v-if="visibleSlots.length === 0"
                    class="text-text-muted text-sm leading-relaxed"
                >
                    {{ t('doctor.patients.medicationListEmpty') }}
                </p>

                <div
                    v-else
                    class="space-y-4"
                >
                    <h2
                        class="text-text-heading text-lg font-semibold md:text-base"
                    >
                        {{ slotListHeading }}
                    </h2>

                    <MedicationIntakeSlotStatusCard
                        v-for="slot in visibleSlots"
                        :key="`${slot.intake_date}-${slot.medication_schedule_id}-${slot.dose_time}`"
                        :intake-slot="slot"
                    />
                </div>
            </template>
        </div>
    </DoctorCollapsibleSection>
</template>
