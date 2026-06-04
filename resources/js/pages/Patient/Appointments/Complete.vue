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
    doctor_visit_summary: '',
});

function submit(): void {
    const trimmed = form.doctor_visit_summary.trim();

    form.transform(() => ({
        status: 'done' as const,
        doctor_visit_summary: trimmed === '' ? null : trimmed,
    })).patch(
        `${route('patient.appointments.update', props.appointment.id)}?outcome_follow_up=done`,
    );
}
</script>

<template>
    <Head>
        <title>{{ t('patient.appointments.doneDialog.title') }}</title>
    </Head>

    <PatientAppointmentOutcomePageLayout
        v-model:schedule-next-open="scheduleNextOpen"
        :title="t('patient.appointments.doneDialog.title')"
        form-id="patient-appointment-complete-form"
        outcome="done"
        :appointment="props.appointment"
        :processing="form.processing"
        :primary-label="t('patient.appointments.doneDialog.confirm')"
        @submit="submit"
    >
        <PatientAppointmentOutcomeFieldCard
            id="appointment-complete-visit-summary"
            v-model="form.doctor_visit_summary"
            :label="t('patient.appointments.doneDialog.visitSummaryLabel')"
            :placeholder="
                t('patient.appointments.doneDialog.visitSummaryPlaceholder')
            "
            :error="form.errors.doctor_visit_summary"
        />
    </PatientAppointmentOutcomePageLayout>
</template>
