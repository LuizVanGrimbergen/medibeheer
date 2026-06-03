<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppointmentCard from '@/Components/Appointments/AppointmentCard.vue';
import AppointmentsPageIntro from '@/Components/Patient/Appointments/AppointmentsPageIntro.vue';
import AppointmentFormDialog from '@/Components/Patient/Appointments/form/AppointmentFormDialog.vue';
import PatientActionSuccessScreen from '@/Components/Patient/PatientActionSuccessScreen.vue';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import { Card, CardContent } from '@/Components/ui/card';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import { usePatientAppointmentsPage } from '@/composables/usePatientAppointmentsPage';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import type { PatientAppointmentsScreenProps } from '@/lib/patient/appointments/screen/patientAppointmentsScreenProps';
import { patientPageSectionTitleClass } from '@/lib/patient/patientPageTypography';

const props = defineProps<PatientAppointmentsScreenProps>();

const { t } = useI18n();

const {
    doctorTypeOptions,
    createSuccessOpen,
    createSuccessTitle,
    createSuccessMessage,
    createSuccessDetails,
    createDialogOpen,
    form,
    editForm,
    editDialogOpen,
    dialogContentClass,
    createStartsAtDateMinIso,
    editSchedulePermitPastStartsAtIfSameInstantMs,
    openAppointmentEditor,
    plannedAppointments,
    hasNoAppointmentsAtAll,
    submitNewAppointment,
    submitAppointmentRevision,
    confirmAndDeleteAppointment,
    isAppointmentUpdateInFlight,
    isAppointmentMarkedDoneInUi,
    reopenScheduledAppointmentAfterCompletion,
} = usePatientAppointmentsPage(props);
</script>

<template>
    <Head>
        <title>{{ t('patient.appointments.title') }}</title>
        <meta
            name="description"
            :content="t('patient.appointments.metaDescription')"
        />
    </Head>

    <PatientLayout>
        <PatientActionSuccessScreen
            v-model:open="createSuccessOpen"
            :title="createSuccessTitle"
            :message="createSuccessMessage"
            :details="createSuccessDetails"
            :done-label="t('patient.actionSuccess.done')"
        />

        <PatientPageShell :title="t('patient.appointments.heading')">
            <AppointmentsPageIntro
                @new-appointment-click="createDialogOpen = true"
            />

            <section class="space-y-5">
                <h2 :class="patientPageSectionTitleClass">
                    {{ t('patient.appointments.plannedHeading') }}
                </h2>

                <ul
                    v-if="plannedAppointments.length > 0"
                    class="flex w-full min-w-0 flex-col gap-5"
                >
                    <li
                        v-for="appointment in plannedAppointments"
                        :key="appointment.id"
                        class="min-w-0"
                    >
                        <AppointmentCard
                            :appointment="appointment"
                            :done-displayed="
                                isAppointmentMarkedDoneInUi(appointment)
                            "
                            :is-patching="
                                isAppointmentUpdateInFlight(appointment.id)
                            "
                            :show-actions="true"
                            :show-transport-section="true"
                            :show-done-toggle="true"
                            :complete-form-href="
                                route(
                                    'patient.appointments.complete',
                                    appointment.id,
                                )
                            "
                            :cancel-form-href="
                                route(
                                    'patient.appointments.cancel',
                                    appointment.id,
                                )
                            "
                            @edit="openAppointmentEditor(appointment)"
                            @delete="confirmAndDeleteAppointment(appointment)"
                            @update:done="
                                (on) => {
                                    if (!on) {
                                        reopenScheduledAppointmentAfterCompletion(
                                            appointment,
                                        );
                                    }
                                }
                            "
                        />
                    </li>
                </ul>

                <NumberedPagination
                    v-if="
                        plannedAppointments.length > 0 &&
                        props.appointments.meta.last_page > 1
                    "
                    route-name="patient.appointments"
                    :meta="props.appointments.meta"
                />

                <Card
                    v-if="plannedAppointments.length === 0"
                    class="border-border bg-surface-2/70 text-text rounded-2xl border-2 border-dashed shadow-none"
                >
                    <CardContent
                        class="text-text-muted px-5 py-14 text-center text-lg leading-relaxed sm:px-8"
                    >
                        {{
                            hasNoAppointmentsAtAll
                                ? t('patient.appointments.empty')
                                : t('patient.appointments.emptyPlanned')
                        }}
                    </CardContent>
                </Card>
            </section>
        </PatientPageShell>

        <AppointmentFormDialog
            :open="createDialogOpen"
            :title="t('patient.appointments.dialogTitle')"
            form-id="appointment-create-form"
            id-prefix="appointment-create"
            :doctor-type-values="doctorTypeOptions"
            :show-doctor-type-placeholder="true"
            :form="form"
            :transport-families="props.linked_families"
            :dialog-content-class="dialogContentClass"
            :starts-at-date-input-min-iso="createStartsAtDateMinIso"
            @update:open="(open) => (createDialogOpen = open)"
            @submit="submitNewAppointment"
            @cancel="createDialogOpen = false"
        />

        <AppointmentFormDialog
            :open="editDialogOpen"
            :title="t('patient.appointments.dialogEditTitle')"
            form-id="appointment-edit-form"
            id-prefix="appointment-edit"
            :doctor-type-values="doctorTypeOptions"
            :show-doctor-type-placeholder="false"
            :form="editForm"
            :transport-families="props.linked_families"
            :dialog-content-class="dialogContentClass"
            :schedule-permit-past-starts-at-if-same-instant-ms="
                editSchedulePermitPastStartsAtIfSameInstantMs
            "
            @update:open="(open) => (editDialogOpen = open)"
            @submit="submitAppointmentRevision"
            @cancel="editDialogOpen = false"
        />
    </PatientLayout>
</template>
