<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import DoctorPatientMedicationSection from '@/Components/Doctor/Patients/DoctorPatientMedicationSection.vue';
import DoctorPatientSnapshotCards from '@/Components/Doctor/Patients/DoctorPatientSnapshotCards.vue';
import DoctorPatientUrgentPrescriptionsPanel from '@/Components/Doctor/Patients/DoctorPatientUrgentPrescriptionsPanel.vue';
import DoctorPatientWellbeingSection from '@/Components/Doctor/Patients/DoctorPatientWellbeingSection.vue';
import { buildDoctorPatientOverviewSnapshot } from '@/lib/doctor/patients/buildDoctorPatientOverviewSnapshot';
import type { DoctorPatientOverviewScreenProps } from '@/lib/doctor/patients/doctorPatientOverviewScreenProps';
import type { MedicationIntakeDayIconStatusValue } from '@/lib/patient/medications/history/medicationIntakeDayPresentation';
import type { DailyMoodScoreValue } from '@/lib/types';

const props = defineProps<DoctorPatientOverviewScreenProps>();

const { t } = useI18n();

const medicationSectionOpen = ref(false);
const medicationStatusFilter = ref<MedicationIntakeDayIconStatusValue | null>(
    null,
);
const medicationSectionRef = ref<InstanceType<
    typeof DoctorPatientMedicationSection
> | null>(null);
const wellbeingSectionOpen = ref(false);
const wellbeingMoodFilter = ref<DailyMoodScoreValue | null>(null);
const wellbeingSectionRef = ref<InstanceType<
    typeof DoctorPatientWellbeingSection
> | null>(null);

const medicationStatusCounts = computed(
    () =>
        buildDoctorPatientOverviewSnapshot(
            props.medication_calendar_days,
            props.wellbeing_calendar_checkins,
        ).medication.statusCounts,
);

function onSnapshotMedicationStatusSelect(
    status: MedicationIntakeDayIconStatusValue,
): void {
    medicationStatusFilter.value = status;
    medicationSectionOpen.value = true;

    nextTick(() => {
        medicationSectionRef.value?.scrollIntoView();
    });
}

function onSnapshotMoodSelect(mood: DailyMoodScoreValue): void {
    wellbeingMoodFilter.value = mood;
    wellbeingSectionOpen.value = true;

    nextTick(() => {
        wellbeingSectionRef.value?.scrollIntoView();
    });
}

watch(
    () =>
        [
            props.selected_patient.public_id,
            props.medication_calendar_month,
            props.wellbeing_calendar_month,
        ] as const,
    () => {
        medicationStatusFilter.value = null;
        medicationSectionOpen.value = false;
        wellbeingMoodFilter.value = null;
        wellbeingSectionOpen.value = false;
    },
);
</script>

<template>
    <section
        class="min-w-0 space-y-5"
        :aria-label="
            t('doctor.patients.overviewHeading', {
                name: props.selected_patient.name,
            })
        "
    >
        <DoctorPatientSnapshotCards
            :calendar-month="props.medication_calendar_month"
            :medication-calendar-days="props.medication_calendar_days"
            :wellbeing-calendar-checkins="props.wellbeing_calendar_checkins"
            :selected-medication-status-filter="medicationStatusFilter"
            :selected-mood-filter="wellbeingMoodFilter"
            @select-medication-status="onSnapshotMedicationStatusSelect"
            @select-mood="onSnapshotMoodSelect"
        />

        <DoctorPatientUrgentPrescriptionsPanel
            :prescriptions="props.urgent_prescriptions"
        />

        <div class="grid min-w-0 gap-5 md:grid-cols-2 md:items-start">
            <DoctorPatientMedicationSection
                ref="medicationSectionRef"
                v-model:open="medicationSectionOpen"
                v-model:status-filter="medicationStatusFilter"
                :medication_calendar_month="props.medication_calendar_month"
                :medication_calendar_days="props.medication_calendar_days"
                :medication_calendar_slots="props.medication_calendar_slots"
                :status-counts="medicationStatusCounts"
            />

            <DoctorPatientWellbeingSection
                ref="wellbeingSectionRef"
                v-model:open="wellbeingSectionOpen"
                v-model:mood-filter="wellbeingMoodFilter"
                :wellbeing_calendar_month="props.wellbeing_calendar_month"
                :wellbeing_calendar_checkins="props.wellbeing_calendar_checkins"
                :wellbeing_checkins="props.wellbeing_checkins"
            />
        </div>
    </section>
</template>
