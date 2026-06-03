<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
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
</script>

<template>
    <Head>
        <title>{{ t('doctor.dashboard.title') }}</title>
    </Head>

    <DoctorLayout>
        <div
            :class="
                cn(
                    'mx-auto flex w-full flex-col gap-6 md:gap-5',
                    hasPatientOverview ? 'max-w-5xl' : 'max-w-2xl',
                )
            "
        >
            <DoctorPatientSearch
                :patients="props.patients"
                :selected-patient-public-id="
                    props.patient_overview?.selected_patient.public_id ?? null
                "
                :autofocus="!hasPatientOverview"
            />

            <DoctorPatientOverviewSection
                v-if="props.patient_overview !== null"
                v-bind="props.patient_overview"
            />
        </div>
    </DoctorLayout>
</template>
