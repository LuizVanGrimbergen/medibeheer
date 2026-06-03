<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyWellbeingCheckinCard from '@/Components/Family/Wellbeing/FamilyWellbeingCheckinCard.vue';
import FamilyWellbeingMonthCalendar from '@/Components/Family/Wellbeing/FamilyWellbeingMonthCalendar.vue';
import HistoryMonthNavigation from '@/Components/History/HistoryMonthNavigation.vue';
import HistorySelectedDaySection from '@/Components/History/HistorySelectedDaySection.vue';
import MedicationIntakeHistorySlotCard from '@/Components/Patient/Medications/MedicationIntakeHistorySlotCard.vue';
import MedicationIntakeMonthCalendar from '@/Components/Patient/Medications/MedicationIntakeMonthCalendar.vue';
import { useHistorySelectedDay } from '@/composables/history/useHistorySelectedDay';
import type { DoctorPatientOverviewScreenProps } from '@/lib/doctor/patients/doctorPatientOverviewScreenProps';
import { indexWellbeingCalendarCheckins } from '@/lib/family/wellbeing/indexWellbeingCalendarCheckins';
import type { MedicationIntakeHistorySlot } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';
import { compareTodayMedicationIntakeSlots } from '@/lib/patient/medications/todayMedicationIntakeDayPeriod';

const props = defineProps<DoctorPatientOverviewScreenProps>();

const { t } = useI18n();

const { selectedCalendarDate, selectedDaySectionRef, onSelectCalendarDate } =
    useHistorySelectedDay(() => props.medication_calendar_month);

const wellbeingCalendarIndex = computed(() =>
    indexWellbeingCalendarCheckins(props.wellbeing_calendar_checkins),
);

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

const selectedDayCheckin = computed(() => {
    const date = selectedCalendarDate.value;

    if (date === null) {
        return undefined;
    }

    return wellbeingCalendarIndex.value.checkinsByDate.get(date);
});
</script>

<template>
    <section
        class="min-w-0 space-y-3"
        :aria-label="
            t('doctor.patients.overviewHeading', {
                name: props.selected_patient.name,
            })
        "
    >
        <HistoryMonthNavigation
            :calendar-month="props.medication_calendar_month"
            navigate-route-name="doctor.dashboard"
            navigate-query-key="calendar_month"
            :prev-month-aria-label="
                t('patient.medications.history.calendar.prevMonth')
            "
            :next-month-aria-label="
                t('patient.medications.history.calendar.nextMonth')
            "
            density="compact"
        />

        <div
            class="grid min-w-0 grid-cols-1 gap-4 md:grid-cols-2 md:items-start md:gap-5"
        >
            <div class="flex min-w-0 flex-col gap-4">
                <MedicationIntakeMonthCalendar
                    :calendar-month="props.medication_calendar_month"
                    :calendar-days="props.medication_calendar_days"
                    :selected-date="selectedCalendarDate"
                    navigate-route-name="doctor.dashboard"
                    navigate-query-key="calendar_month"
                    density="compact"
                    :show-month-navigation="false"
                    :header-title="
                        t('patient.medications.history.calendar.title')
                    "
                    @select-date="onSelectCalendarDate"
                />

                <HistorySelectedDaySection
                    ref="selectedDaySectionRef"
                    :selected-date="selectedCalendarDate"
                    :heading="
                        t('patient.medications.history.selectedDayHeading')
                    "
                    density="compact"
                    :show-heading="false"
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

                    <div v-else class="flex flex-col gap-3">
                        <MedicationIntakeHistorySlotCard
                            v-for="slot in selectedDaySlots"
                            :key="`${slot.medication_schedule_id}-${slot.dose_time}`"
                            :intake-slot="slot"
                            density="compact"
                        />
                    </div>
                </HistorySelectedDaySection>
            </div>

            <div class="flex min-w-0 flex-col gap-4">
                <FamilyWellbeingMonthCalendar
                    :calendar-month="props.wellbeing_calendar_month"
                    :moods-by-date="wellbeingCalendarIndex.moodsByDate"
                    :selected-date="selectedCalendarDate"
                    navigate-route-name="doctor.dashboard"
                    density="compact"
                    :show-month-navigation="false"
                    :header-title="t('family.wellbeing.calendar.title')"
                    @select-date="onSelectCalendarDate"
                />

                <HistorySelectedDaySection
                    :selected-date="selectedCalendarDate"
                    :heading="t('family.wellbeing.selectedDayHeading')"
                    density="compact"
                    :show-heading="false"
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
            </div>
        </div>
    </section>
</template>
