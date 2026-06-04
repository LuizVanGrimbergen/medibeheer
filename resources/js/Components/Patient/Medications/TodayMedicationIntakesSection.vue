<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import TodayMedicationIntakeCard from '@/Components/Patient/Medications/TodayMedicationIntakeCard.vue';
import TodayMedicationIntakeDayPeriodHeading from '@/Components/Patient/Medications/TodayMedicationIntakeDayPeriodHeading.vue';
import TodayTakenMedicationIntakesSection from '@/Components/Patient/Medications/TodayTakenMedicationIntakesSection.vue';
import {
    buildTodayMedicationIntakePeriodSections,
    partitionTodayMedicationIntakes,
} from '@/lib/patient/medications/todayMedicationIntakeDayPeriod';
import type {
    TodayMedicationIntakeDayPeriodValue,
    TodayMedicationIntakeSlot,
} from '@/lib/types';

const props = withDefaults(
    defineProps<{
        slots?: TodayMedicationIntakeSlot[];
    }>(),
    {
        slots: () => [],
    },
);

const { t } = useI18n();

const periodSections = computed(() =>
    buildTodayMedicationIntakePeriodSections(
        partitionTodayMedicationIntakes(props.slots),
    ),
);

function slotKey(slot: TodayMedicationIntakeSlot): string {
    return `${slot.medication_schedule_id}-${slot.dose_time}`;
}

function periodTitle(period: TodayMedicationIntakeDayPeriodValue): string {
    return t(`patient.dashboard.todayMedications.periods.${period}.title`);
}
</script>

<template>
    <section
        v-if="props.slots.length > 0"
        class="space-y-3 sm:space-y-4"
        :aria-label="t('patient.dashboard.todayMedications.title')"
    >
        <div class="flex flex-col gap-6 sm:gap-8">
            <section
                v-for="section in periodSections"
                :key="section.period"
                class="space-y-3 sm:space-y-4"
                :aria-label="periodTitle(section.period)"
            >
                <TodayMedicationIntakeDayPeriodHeading
                    :period="section.period"
                />

                <ul
                    v-if="section.pendingSlots.length > 0"
                    class="flex w-full min-w-0 flex-col gap-5"
                >
                    <li
                        v-for="slot in section.pendingSlots"
                        :key="slotKey(slot)"
                        class="min-w-0"
                    >
                        <TodayMedicationIntakeCard :intake-slot="slot" />
                    </li>
                </ul>

                <TodayTakenMedicationIntakesSection
                    v-if="section.takenSlots.length > 0"
                    :taken-slots="section.takenSlots"
                />
            </section>
        </div>
    </section>
</template>
