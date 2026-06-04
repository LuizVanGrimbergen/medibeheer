import {
    formatAppointmentAddress,
    type AppointmentAddressFields,
} from '@/lib/appointments/formatAppointmentAddress';

export function googleMapsSearchUrlForAppointmentAddress(
    appointment: AppointmentAddressFields,
): string | null {
    const query = formatAppointmentAddress(appointment).trim();

    if (query.length < 1) {
        return null;
    }

    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(query)}`;
}
