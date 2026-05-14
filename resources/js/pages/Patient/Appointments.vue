<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CalendarPlus } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import AppointmentCard from '@/Components/Appointments/AppointmentCard.vue';
import AppointmentFormDialog from '@/Components/Patient/Appointments/form/AppointmentFormDialog.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import { usePatientAppointmentsPage } from '@/composables/usePatientAppointmentsPage';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import type { PatientAppointmentsScreenProps } from '@/lib/patient/appointments/screen/patientAppointmentsScreenProps';

const props = defineProps<PatientAppointmentsScreenProps>();

const { t } = useI18n();

const {
    doctorTypeOptions,
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
    </Head>

    <PatientLayout>
        <div class="flex min-w-0 w-full flex-col gap-10">
            <div
                class="flex min-w-0 w-full flex-col gap-5 sm:flex-row sm:items-start sm:justify-between sm:gap-6"
            >
                <div class="min-w-0">
                    <h1 class="text-3xl font-bold leading-tight text-text-heading">
                        {{ t('patient.appointments.heading') }}
                    </h1>
                    <p class="mt-3 max-w-2xl text-base leading-relaxed text-text-muted">
                        {{ t('patient.appointments.plannedDescription') }}
                    </p>
                </div>

                <Button
                    size="lg"
                    class="min-h-14 w-full touch-manipulation gap-2.5 self-stretch px-6 text-lg sm:w-auto sm:self-center sm:px-8"
                    @click="createDialogOpen = true"
                >
                    <CalendarPlus
                        class="size-6 shrink-0"
                        aria-hidden="true"
                    />
                    {{ t('patient.appointments.newAppointment') }}
                </Button>
            </div>

            <section class="space-y-5">
                <h2 class="text-2xl font-bold leading-tight text-text-heading">
                    {{ t('patient.appointments.plannedHeading') }}
                </h2>

                <ul
                    v-if="plannedAppointments.length > 0"
                    class="flex min-w-0 w-full flex-col gap-5"
                >
                    <li
                        v-for="appointment in plannedAppointments"
                        :key="appointment.id"
                        class="min-w-0"
                    >
                        <AppointmentCard
                            :appointment="appointment"
                            :done-displayed="isAppointmentMarkedDoneInUi(appointment)"
                            :is-patching="isAppointmentUpdateInFlight(appointment.id)"
                            :show-actions="true"
                            :show-transport-section="true"
                            :show-done-toggle="true"
                            :complete-form-href="route('patient.appointments.complete', appointment.id)"
                            :cancel-form-href="route('patient.appointments.cancel', appointment.id)"
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
                    class="rounded-2xl border-2 border-dashed border-border bg-surface-2/70 text-text shadow-none"
                >
                    <CardContent class="px-5 py-14 text-center text-lg leading-relaxed text-text-muted sm:px-8">
                        {{
                            hasNoAppointmentsAtAll
                                ? t('patient.appointments.empty')
                                : t('patient.appointments.emptyPlanned')
                        }}
                    </CardContent>
                </Card>
            </section>
        </div>

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
