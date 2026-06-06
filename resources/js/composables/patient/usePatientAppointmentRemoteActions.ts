import type { RequestPayload } from '@inertiajs/core';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type {
    AppointmentStatusValue,
    Appointment as PatientAppointment,
} from '@/lib/types';

type DispatchAppointmentUpdateOptions = {
    clearOptimisticDoneStateWhenFinished?: boolean;
};

export function usePatientAppointmentRemoteActions() {
    const appointmentIdsAwaitingResponse = ref<number[]>([]);
    const optimisticDoneUiByAppointmentId = ref<
        Partial<Record<number, boolean>>
    >({});

    function isAppointmentUpdateInFlight(appointmentId: number): boolean {
        return appointmentIdsAwaitingResponse.value.includes(appointmentId);
    }

    function isAppointmentMarkedDoneInUi(
        appointment: PatientAppointment,
    ): boolean {
        const optimistic =
            optimisticDoneUiByAppointmentId.value[appointment.id];

        if (optimistic !== undefined) {
            return optimistic;
        }

        return appointment.status === 'done';
    }

    function dispatchAppointmentUpdate(
        appointmentId: number,
        payload: RequestPayload,
        options: DispatchAppointmentUpdateOptions = {},
    ): void {
        router.patch(
            route('patient.appointments.update', appointmentId),
            payload,
            {
                preserveScroll: true,
                onStart: () => {
                    appointmentIdsAwaitingResponse.value = [
                        ...appointmentIdsAwaitingResponse.value,
                        appointmentId,
                    ];
                },
                onFinish: () => {
                    appointmentIdsAwaitingResponse.value =
                        appointmentIdsAwaitingResponse.value.filter(
                            (id) => id !== appointmentId,
                        );

                    if (!options.clearOptimisticDoneStateWhenFinished) {
                        return;
                    }

                    const next = { ...optimisticDoneUiByAppointmentId.value };
                    delete next[appointmentId];
                    optimisticDoneUiByAppointmentId.value = next;
                },
            },
        );
    }

    const deleteDialogOpen = ref(false);
    const appointmentPendingDelete = ref<PatientAppointment | null>(null);
    const deleteProcessing = ref(false);

    function openDeleteAppointmentDialog(
        appointment: PatientAppointment,
    ): void {
        appointmentPendingDelete.value = appointment;
        deleteDialogOpen.value = true;
    }

    function closeDeleteAppointmentDialog(): void {
        deleteDialogOpen.value = false;
        appointmentPendingDelete.value = null;
    }

    function confirmDeleteAppointment(): void {
        const appointment = appointmentPendingDelete.value;

        if (appointment === null) {
            return;
        }

        deleteProcessing.value = true;

        router.delete(route('patient.appointments.destroy', appointment.id), {
            preserveScroll: true,
            onSuccess: () => {
                closeDeleteAppointmentDialog();
            },
            onFinish: () => {
                deleteProcessing.value = false;
            },
        });
    }

    function reopenScheduledAppointmentAfterCompletion(
        appointment: PatientAppointment,
    ): void {
        if (isAppointmentUpdateInFlight(appointment.id)) {
            return;
        }

        if (!isAppointmentMarkedDoneInUi(appointment)) {
            return;
        }

        optimisticDoneUiByAppointmentId.value = {
            ...optimisticDoneUiByAppointmentId.value,
            [appointment.id]: false,
        };

        dispatchAppointmentUpdate(
            appointment.id,
            {
                status: 'scheduled' as AppointmentStatusValue,
                doctor_visit_summary: null,
            },
            { clearOptimisticDoneStateWhenFinished: true },
        );
    }

    return {
        isAppointmentUpdateInFlight,
        isAppointmentMarkedDoneInUi,
        openDeleteAppointmentDialog,
        closeDeleteAppointmentDialog,
        confirmDeleteAppointment,
        deleteDialogOpen,
        appointmentPendingDelete,
        deleteProcessing,
        reopenScheduledAppointmentAfterCompletion,
    };
}
