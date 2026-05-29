<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import DoctorIncomingPatientInvitationsSection from '@/Components/Doctor/Patients/DoctorIncomingPatientInvitationsSection.vue';
import DoctorLinkedPatientsSection from '@/Components/Doctor/Patients/DoctorLinkedPatientsSection.vue';
import DoctorLayout from '@/Layouts/DoctorLayout.vue';
import type { IncomingDoctorInvitation, LinkedPatient } from '@/lib/types';

withDefaults(
    defineProps<{
        patients: LinkedPatient[];
        incoming_invitations?: IncomingDoctorInvitation[];
    }>(),
    {
        incoming_invitations: () => [],
    },
);

const { t } = useI18n();
</script>

<template>
    <Head>
        <title>{{ t('doctor.patients.title') }}</title>
    </Head>

    <DoctorLayout>
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 md:gap-5">
            <h1 class="text-2xl font-semibold text-text-heading">
                {{ t('doctor.patients.heading') }}
            </h1>

            <DoctorLinkedPatientsSection :patients="patients" />

            <DoctorIncomingPatientInvitationsSection
                :invitations="incoming_invitations"
            />
        </div>
    </DoctorLayout>
</template>
