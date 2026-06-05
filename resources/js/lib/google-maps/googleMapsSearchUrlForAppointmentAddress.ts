import type { AppointmentAddressFields } from '@/lib/appointments/formatAppointmentAddress';
import { formatAppointmentAddress } from '@/lib/appointments/formatAppointmentAddress';

function appointmentAddressQuery(
    appointment: AppointmentAddressFields,
): string | null {
    const query = formatAppointmentAddress(appointment).trim();

    if (query.length < 1) {
        return null;
    }

    return query;
}

export function googleMapsSearchUrlForAppointmentAddress(
    appointment: AppointmentAddressFields,
): string | null {
    const query = appointmentAddressQuery(appointment);

    if (query === null) {
        return null;
    }

    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(query)}`;
}

export function googleMapsDirectionsUrlForAppointmentAddress(
    appointment: AppointmentAddressFields,
): string | null {
    const query = appointmentAddressQuery(appointment);

    if (query === null) {
        return null;
    }

    return `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(query)}`;
}
