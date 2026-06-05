<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import DoctorPatientContextHeader from '@/Components/Doctor/Patients/DoctorPatientContextHeader.vue';
import DoctorPatientOverviewSection from '@/Components/Doctor/Patients/DoctorPatientOverviewSection.vue';
import DoctorPatientSearch from '@/Components/Doctor/Patients/DoctorPatientSearch.vue';
import DoctorLayout from '@/Layouts/DoctorLayout.vue';
import type { DoctorPatientOverviewScreenProps } from '@/lib/doctor/patients/doctorPatientOverviewScreenProps';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        patients: { public_id: string; name: string }[];
        patient_overview?: DoctorPatientOverviewScreenProps | null;
    }>(),
    {
        patient_overview: null,
    },
);

const { t } = useI18n();

const hasPatientOverview = computed(() => props.patient_overview !== null);
const patientSearchExpanded = ref(false);

watch(
    () => props.patient_overview?.selected_patient.public_id,
    () => {
        patientSearchExpanded.value = false;
    },
);
</script>

<template>
    <Head>
        <title>{{ t('doctor.dashboard.title') }}</title>
    </Head>

    <DoctorLayout>
        <div
            :class="
                cn(
                    'mx-auto flex w-full flex-col gap-5 md:gap-4',
                    hasPatientOverview ? 'max-w-7xl' : 'max-w-2xl',
                )
            "
        >
            <DoctorPatientSearch
                v-if="!hasPatientOverview || patientSearchExpanded"
                v-model:expanded="patientSearchExpanded"
                :patients="props.patients"
                :selected-patient-public-id="
                    props.patient_overview?.selected_patient.public_id ?? null
                "
                :autofocus="!hasPatientOverview || patientSearchExpanded"
            />

            <template v-if="props.patient_overview !== null">
                <DoctorPatientContextHeader
                    :patient-name="props.patient_overview.selected_patient.name"
                    @switch-patient="patientSearchExpanded = true"
                />

                <DoctorPatientOverviewSection v-bind="props.patient_overview" />
            </template>
        </div>
    </DoctorLayout>
</template>
