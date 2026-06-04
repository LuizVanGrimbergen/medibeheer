<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientAppointmentScheduleNextSuccessScreen from '@/Components/Patient/Appointments/PatientAppointmentScheduleNextSuccessScreen.vue';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import type { PatientAppointmentScheduleNextPageProps } from '@/lib/patient/appointments/screen/patientAppointmentScheduleNextPageProps';

const props = defineProps<PatientAppointmentScheduleNextPageProps>();

const { t } = useI18n();

const scheduleNextOpen = ref(true);
</script>

<template>
    <Head>
        <title>{{ t('patient.appointments.scheduleNext.title') }}</title>
    </Head>

    <PatientLayout>
        <PatientAppointmentScheduleNextSuccessScreen
            v-model:open="scheduleNextOpen"
            :outcome="props.outcome"
        />
    </PatientLayout>
</template>
