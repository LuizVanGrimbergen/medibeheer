import { utcIsoToLocalDatetimeCompositeForForm } from '@/lib/patient/appointments/patientAppointmentStartsAtForFormInputs';
import type { Appointment as PatientAppointment } from '@/lib/types';

type LinkedFamily = {
    id: number;
};

export function patientAppointmentToEditFormState(
    appointment: PatientAppointment,
    linkedFamilies: LinkedFamily[],
): {
    doctor_type: PatientAppointment['doctor_type'];
    provider_name: string;
    address: string;
    starts_at_date: string;
    starts_at_time: string;
    notes: string;
    needs_transport: boolean;
    transport_family_ids: number[];
    status: PatientAppointment['status'];
} {
    const startsLocal = utcIsoToLocalDatetimeCompositeForForm(appointment.starts_at);
    const [startsDate, startsTimeRaw] = startsLocal.split('T');

    let transportFamilyIds: number[] = [];

    if (appointment.transport_invited_family_ids.length > 0) {
        transportFamilyIds = [...appointment.transport_invited_family_ids];
    } else if (appointment.transport_family === null) {
        transportFamilyIds = linkedFamilies.map((f) => f.id);
    } else {
        transportFamilyIds = [appointment.transport_family.id];
    }

    return {
        doctor_type: appointment.doctor_type,
        provider_name: appointment.provider_name,
        address: appointment.address,
        starts_at_date: startsDate ?? '',
        starts_at_time: startsTimeRaw ? startsTimeRaw.slice(0, 5) : '',
        notes: appointment.notes ?? '',
        needs_transport: appointment.needs_transport ?? false,
        transport_family_ids: transportFamilyIds,
        status: appointment.status,
    };
}

