<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import DoctorPatientContextHeader from '@/Components/Doctor/Patients/DoctorPatientContextHeader.vue';
import DoctorPatientOverviewSection from '@/Components/Doctor/Patients/DoctorPatientOverviewSection.vue';
import DoctorPatientSearch from '@/Components/Doctor/Patients/DoctorPatientSearch.vue';
import ListCardSkeleton from '@/Components/ui/skeleton/ListCardSkeleton.vue';
import DoctorLayout from '@/Layouts/DoctorLayout.vue';
import type { DoctorPatientOverviewScreenProps } from '@/lib/doctor/patients/doctorPatientOverviewScreenProps';
import { isDeferredInertiaPropLoading } from '@/lib/inertia/isDeferredInertiaPropLoading';
import { cn } from '@/lib/utils';

const props = defineProps<{
    patients?: { public_id: string; name: string }[];
    patient_overview?: DoctorPatientOverviewScreenProps | null;
}>();

const { t } = useI18n();

const isPatientsLoading = computed(() =>
    isDeferredInertiaPropLoading(props.patients),
);

const isPatientOverviewLoading = computed(() =>
    isDeferredInertiaPropLoading(props.patient_overview),
);

const hasPatientOverview = computed(
    () =>
        props.patient_overview !== null &&
        props.patient_overview !== undefined,
);
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
            <ListCardSkeleton v-if="isPatientsLoading" />

            <DoctorPatientSearch
                v-else-if="!hasPatientOverview || patientSearchExpanded"
                v-model:expanded="patientSearchExpanded"
                :patients="props.patients ?? []"
                :selected-patient-public-id="
                    props.patient_overview?.selected_patient.public_id ?? null
                "
                :autofocus="!hasPatientOverview || patientSearchExpanded"
            />

            <ListCardSkeleton v-if="isPatientOverviewLoading" />

            <template v-else-if="hasPatientOverview">
                <DoctorPatientContextHeader
                    :patient-name="
                        props.patient_overview!.selected_patient.name
                    "
                    @switch-patient="patientSearchExpanded = true"
                />

                <DoctorPatientOverviewSection
                    v-bind="props.patient_overview!"
                />
            </template>
        </div>
    </DoctorLayout>
</template>
