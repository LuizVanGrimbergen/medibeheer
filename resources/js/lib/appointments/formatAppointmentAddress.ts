export type AppointmentAddressFields = {
    street: string | null;
    house_number: string | null;
    postal_code: string | null;
    city: string | null;
};

export function formatAppointmentAddress(
    appointment: AppointmentAddressFields,
): string {
    const street = (appointment.street ?? '').trim();
    const houseNumber = (appointment.house_number ?? '').trim();
    const line1 = [street, houseNumber].filter(Boolean).join(' ');
    const postal = (appointment.postal_code ?? '').trim();
    const city = (appointment.city ?? '').trim();
    const line2 = [postal, city].filter(Boolean).join(' ');

    return [line1, line2].filter(Boolean).join(', ');
}
