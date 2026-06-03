import { computed } from 'vue';
import { usePatientAppointmentFormDialogs } from '@/composables/usePatientAppointmentFormDialogs';
import { usePatientAppointmentRemoteActions } from '@/composables/usePatientAppointmentRemoteActions';
import type { PatientAppointmentsScreenProps } from '@/lib/patient/appointments/screen/patientAppointmentsScreenProps';

export type PatientAppointmentsPageProps = PatientAppointmentsScreenProps;

export function usePatientAppointmentsPage(
    props: PatientAppointmentsScreenProps,
) {
    const formDialogs = usePatientAppointmentFormDialogs(props);
    const remoteActions = usePatientAppointmentRemoteActions();

    const plannedAppointments = computed(() => props.appointments.data);

    const hasNoAppointmentsAtAll = computed(
        () =>
            props.appointment_tab_totals.planned === 0 &&
            props.appointment_tab_totals.completed === 0,
    );

    return {
        doctorTypeOptions: formDialogs.doctorTypeOptions,
        createSuccessOpen: formDialogs.createSuccessOpen,
        createSuccessTitle: formDialogs.createSuccessTitle,
        createSuccessMessage: formDialogs.createSuccessMessage,
        createSuccessDetails: formDialogs.createSuccessDetails,
        createDialogOpen: formDialogs.createDialogOpen,
        form: formDialogs.createForm,
        editForm: formDialogs.editForm,
        editDialogOpen: formDialogs.editDialogOpen,
        dialogContentClass: formDialogs.appointmentFormDialogLayoutClass,
        createStartsAtDateMinIso: formDialogs.createStartsAtDateMinIso,
        editSchedulePermitPastStartsAtIfSameInstantMs:
            formDialogs.editSchedulePermitPastStartsAtIfSameInstantMs,
        openAppointmentEditor: formDialogs.openAppointmentEditor,
        plannedAppointments,
        hasNoAppointmentsAtAll,
        submitNewAppointment: formDialogs.submitNewAppointment,
        submitAppointmentRevision: formDialogs.submitAppointmentRevision,
        confirmAndDeleteAppointment: remoteActions.confirmAndDeleteAppointment,
        isAppointmentUpdateInFlight: remoteActions.isAppointmentUpdateInFlight,
        isAppointmentMarkedDoneInUi: remoteActions.isAppointmentMarkedDoneInUi,
        reopenScheduledAppointmentAfterCompletion:
            remoteActions.reopenScheduledAppointmentAfterCompletion,
    };
}
