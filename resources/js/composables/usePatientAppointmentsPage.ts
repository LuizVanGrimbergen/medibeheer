import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { usePatientAppointmentFormDialogs } from '@/composables/usePatientAppointmentFormDialogs';
import { usePatientAppointmentRemoteActions } from '@/composables/usePatientAppointmentRemoteActions';
import type {
    PatientAppointmentView,
    PatientAppointmentsScreenProps,
} from '@/lib/patient/appointments/patientAppointmentsScreenProps';

export type PatientAppointmentsPageProps = PatientAppointmentsScreenProps;

export function setPatientAppointmentViewFromToggle(
    next: string,
    current: PatientAppointmentView,
): void {
    if (next !== 'planned' && next !== 'completed') {
        return;
    }

    if (next === current) {
        return;
    }

    router.get(
        route('patient.appointments', { view: next, page: 1 }),
        {},
        { preserveState: true, preserveScroll: true },
    );
}

export function usePatientAppointmentsPage(props: PatientAppointmentsScreenProps) {
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
        setAppointmentViewFromToggle: setPatientAppointmentViewFromToggle,
        submitNewAppointment: formDialogs.submitNewAppointment,
        submitAppointmentRevision: formDialogs.submitAppointmentRevision,
        confirmAndDeleteAppointment: remoteActions.confirmAndDeleteAppointment,
        isAppointmentUpdateInFlight: remoteActions.isAppointmentUpdateInFlight,
        isAppointmentMarkedDoneInUi: remoteActions.isAppointmentMarkedDoneInUi,
        reopenScheduledAppointmentAfterCompletion:
            remoteActions.reopenScheduledAppointmentAfterCompletion,
    };
}
