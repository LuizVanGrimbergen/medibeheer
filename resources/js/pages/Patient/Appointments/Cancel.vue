<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientAppointmentOutcomeFieldCard from '@/Components/Patient/Appointments/outcome/PatientAppointmentOutcomeFieldCard.vue';
import PatientAppointmentOutcomePageLayout from '@/Components/Patient/Appointments/outcome/PatientAppointmentOutcomePageLayout.vue';
import type { PatientAppointmentOutcomePageProps } from '@/lib/patient/appointments/screen/patientAppointmentOutcomePageProps';

const props = defineProps<PatientAppointmentOutcomePageProps>();

const { t } = useI18n();

const scheduleNextOpen = ref(props.show_schedule_next_prompt === true);

const form = useForm({
    cancellation_reason: '',
});

function submit(): void {
    const trimmed = form.cancellation_reason.trim();

    form.transform(() => ({
        status: 'cancelled' as const,
        cancellation_reason: trimmed === '' ? null : trimmed,
    })).patch(
        `${route('patient.appointments.update', props.appointment.id)}?outcome_follow_up=cancelled`,
    );
}
</script>

<template>
    <Head>
        <title>{{ t('patient.appointments.cancelDialog.title') }}</title>
    </Head>

    <PatientAppointmentOutcomePageLayout
        v-model:schedule-next-open="scheduleNextOpen"
        :title="t('patient.appointments.cancelDialog.title')"
        form-id="patient-appointment-cancel-form"
        outcome="cancelled"
        :appointment="props.appointment"
        :processing="form.processing"
        :primary-label="t('patient.appointments.cancelDialog.confirm')"
        @submit="submit"
    >
        <PatientAppointmentOutcomeFieldCard
            id="appointment-cancel-reason"
            v-model="form.cancellation_reason"
            :label="t('patient.appointments.cancelDialog.reasonLabel')"
            :placeholder="
                t('patient.appointments.cancelDialog.reasonPlaceholder')
            "
            :error="form.errors.cancellation_reason"
        />
    </PatientAppointmentOutcomePageLayout>
</template>
