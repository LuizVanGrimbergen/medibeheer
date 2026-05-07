import { computed } from 'vue';
import { usePatientAppointmentFormDialogs } from '@/composables/usePatientAppointmentFormDialogs';
import { usePatientAppointmentRemoteActions } from '@/composables/usePatientAppointmentRemoteActions';
import type { PatientAppointmentsScreenProps } from '@/lib/patient/appointments/patientAppointmentsScreenProps';

export type PatientAppointmentsPageProps = PatientAppointmentsScreenProps;

export function usePatientAppointmentsPage(props: PatientAppointmentsScreenProps) {
    const formDialogs = usePatientAppointmentFormDialogs(props);
    const remoteActions = usePatientAppointmentRemoteActions();

    const plannedAppointments = computed(() => props.appointments.data);

    const hasNoAppointmentsAtAll = computed(() => props.appointments.meta.total === 0);

    return {
        doctorTypeOptions: formDialogs.doctorTypeOptions,
        createDialogOpen: formDialogs.createDialogOpen,
        form: formDialogs.createForm,
        editForm: formDialogs.editForm,
        editDialogOpen: formDialogs.editDialogOpen,
        createSubmitDisabled: formDialogs.createSubmitDisabled,
        editSubmitDisabled: formDialogs.editSubmitDisabled,
        dialogContentClass: formDialogs.appointmentFormDialogLayoutClass,
        openAppointmentEditor: formDialogs.openAppointmentEditor,
        plannedAppointments,
        hasNoAppointmentsAtAll,
        submitNewAppointment: formDialogs.submitNewAppointment,
        submitAppointmentRevision: formDialogs.submitAppointmentRevision,
        confirmAndDeleteAppointment: remoteActions.confirmAndDeleteAppointment,
        isAppointmentUpdateInFlight: remoteActions.isAppointmentUpdateInFlight,
        isAppointmentMarkedDoneInUi: remoteActions.isAppointmentMarkedDoneInUi,
        submitAppointmentCancellation: remoteActions.submitAppointmentCancellation,
        submitAppointmentCompletion: remoteActions.submitAppointmentCompletion,
        reopenScheduledAppointmentAfterCompletion:
            remoteActions.reopenScheduledAppointmentAfterCompletion,
    };
}
