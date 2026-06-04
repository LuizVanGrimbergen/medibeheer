export type AppointmentAddressFormValues = {
    street: string;
    postal_code: string;
    city: string;
};

export function isAppointmentAddressComplete(
    values: AppointmentAddressFormValues,
): boolean {
    return (
        values.street.trim().length > 0 &&
        values.postal_code.trim().length > 0 &&
        values.city.trim().length > 0
    );
}
