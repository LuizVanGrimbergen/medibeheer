import type { Appointment as PatientAppointment } from '@/lib/types';

type LinkedFamily = {
    id: number;
};

function utcIsoToLocalDateAndTime(isoInstant: string): { date: string; time: string } {
    const parsed = new Date(isoInstant);

    if (Number.isNaN(parsed.getTime())) {
        return { date: '', time: '' };
    }

    const pad = (n: number) => String(n).padStart(2, '0');

    return {
        date: `${parsed.getFullYear()}-${pad(parsed.getMonth() + 1)}-${pad(parsed.getDate())}`,
        time: `${pad(parsed.getHours())}:${pad(parsed.getMinutes())}`,
    };
}

function resolveTransportFamilyIdsForEdit(
    appointment: PatientAppointment,
    linkedFamilies: LinkedFamily[],
): number[] {
    if (linkedFamilies.length === 0) {
        return [];
    }

    if (appointment.transport_invited_family_ids.length > 0) {
        return [...appointment.transport_invited_family_ids];
    }

    if (appointment.transport_family === null) {
        return linkedFamilies.map((f) => f.id);
    }

    return [appointment.transport_family.id];
}

export function patientAppointmentToEditFormState(
    appointment: PatientAppointment,
    linkedFamilies: LinkedFamily[],
): {
    doctor_type: PatientAppointment['doctor_type'];
    provider_name: string;
    street: string;
    house_number: string;
    postal_code: string;
    city: string;
    starts_at_date: string;
    starts_at_time: string;
    notes: string;
    needs_transport: boolean;
    transport_family_ids: number[];
    status: PatientAppointment['status'];
} {
    const { date: startsAtDate, time: startsAtTime } = utcIsoToLocalDateAndTime(appointment.starts_at);

    const needsTransport = linkedFamilies.length === 0
        ? false
        : (appointment.needs_transport ?? false);

    return {
        doctor_type: appointment.doctor_type,
        provider_name: appointment.provider_name,
        street: appointment.street,
        house_number: appointment.house_number,
        postal_code: appointment.postal_code,
        city: appointment.city,
        starts_at_date: startsAtDate,
        starts_at_time: startsAtTime,
        notes: appointment.notes ?? '',
        needs_transport: needsTransport,
        transport_family_ids: resolveTransportFamilyIdsForEdit(appointment, linkedFamilies),
        status: appointment.status,
    };
}
