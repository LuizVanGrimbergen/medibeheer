import type { RequestPayload } from '@inertiajs/core';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import type {
    Appointment as PatientAppointment,
    AppointmentCancelledCommitPayload,
    AppointmentDoneCommitPayload,
    AppointmentStatusValue,
} from '@/lib/types';

type DispatchAppointmentUpdateOptions = {
    clearOptimisticDoneStateWhenFinished?: boolean;
};

export function usePatientAppointmentRemoteActions() {
    const { t } = useI18n();

    const appointmentIdsAwaitingResponse = ref<number[]>([]);
    const optimisticDoneUiByAppointmentId = ref<Partial<Record<number, boolean>>>({});

    function isAppointmentUpdateInFlight(appointmentId: number): boolean {
        return appointmentIdsAwaitingResponse.value.includes(appointmentId);
    }

    function isAppointmentMarkedDoneInUi(appointment: PatientAppointment): boolean {
        const optimistic = optimisticDoneUiByAppointmentId.value[appointment.id];

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
        router.patch(route('patient.appointments.update', appointmentId), payload, {
            preserveScroll: true,
            onStart: () => {
                appointmentIdsAwaitingResponse.value = [
                    ...appointmentIdsAwaitingResponse.value,
                    appointmentId,
                ];
            },
            onFinish: () => {
                appointmentIdsAwaitingResponse.value =
                    appointmentIdsAwaitingResponse.value.filter((id) => id !== appointmentId);

                if (!options.clearOptimisticDoneStateWhenFinished) {
                    return;
                }

                const next = { ...optimisticDoneUiByAppointmentId.value };
                delete next[appointmentId];
                optimisticDoneUiByAppointmentId.value = next;
            },
        });
    }

    function confirmAndDeleteAppointment(appointment: PatientAppointment): void {
        if (!confirm(t('patient.appointments.deleteConfirm'))) {
            return;
        }

        router.delete(route('patient.appointments.destroy', appointment.id), {
            preserveScroll: true,
        });
    }

    function submitAppointmentCancellation(
        appointment: PatientAppointment,
        payload: AppointmentCancelledCommitPayload,
    ): void {
        if (isAppointmentUpdateInFlight(appointment.id)) {
            return;
        }

        dispatchAppointmentUpdate(appointment.id, {
            status: 'cancelled' as AppointmentStatusValue,
            cancellation_reason: payload.cancellation_reason,
        });
    }

    function submitAppointmentCompletion(
        appointment: PatientAppointment,
        payload: AppointmentDoneCommitPayload,
    ): void {
        if (isAppointmentUpdateInFlight(appointment.id)) {
            return;
        }

        if (isAppointmentMarkedDoneInUi(appointment)) {
            return;
        }

        optimisticDoneUiByAppointmentId.value = {
            ...optimisticDoneUiByAppointmentId.value,
            [appointment.id]: true,
        };

        dispatchAppointmentUpdate(
            appointment.id,
            {
                status: 'done' as AppointmentStatusValue,
                doctor_visit_summary: payload.doctor_visit_summary,
            },
            { clearOptimisticDoneStateWhenFinished: true },
        );
    }

    function reopenScheduledAppointmentAfterCompletion(appointment: PatientAppointment): void {
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
        confirmAndDeleteAppointment,
        submitAppointmentCancellation,
        submitAppointmentCompletion,
        reopenScheduledAppointmentAfterCompletion,
    };
}
