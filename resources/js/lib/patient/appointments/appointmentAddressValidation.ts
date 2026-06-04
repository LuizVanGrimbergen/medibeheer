import appointmentsNl from '@/translations/nl/patient/appointments';

export type AppointmentAddressFieldValues = {
    street: string;
    postal_code: string;
    city: string;
};

export type AppointmentAddressValidationValues = AppointmentAddressFieldValues & {
    house_number?: string;
};

export type AppointmentAddressFieldErrorKey = 'street' | 'postal_code' | 'city';

export type AppointmentAddressFieldErrors = Partial<
    Record<AppointmentAddressFieldErrorKey, string>
>;

export function isBelgianPostalCodeValid(postalCode: string): boolean {
    return /^\d{4}$/.test(postalCode.trim());
}

export function isAppointmentAddressProvided(
    values: AppointmentAddressValidationValues,
): boolean {
    const houseNumber = values.house_number?.trim() ?? '';

    return (
        values.street.trim().length > 0 ||
        values.postal_code.trim().length > 0 ||
        values.city.trim().length > 0 ||
        houseNumber.length > 0
    );
}

export function isAppointmentAddressValidationRequired(
    needsTransport: boolean,
    values: AppointmentAddressValidationValues,
): boolean {
    return needsTransport || isAppointmentAddressProvided(values);
}

export function collectAppointmentAddressFieldErrors(
    values: AppointmentAddressFieldValues,
    options?: { required?: boolean },
): AppointmentAddressFieldErrors {
    if (options?.required === false) {
        return {};
    }

    const out: AppointmentAddressFieldErrors = {};

    if (values.street.trim().length < 1) {
        out.street = appointmentsNl.stepValidation.streetRequired;
    }

    const postalTrimmed = values.postal_code.trim();

    if (postalTrimmed.length < 1) {
        out.postal_code = appointmentsNl.stepValidation.postalCodeRequired;
    } else if (!isBelgianPostalCodeValid(postalTrimmed)) {
        out.postal_code = appointmentsNl.validation.postalCodeBelgianInvalid;
    }

    if (values.city.trim().length < 1) {
        out.city = appointmentsNl.stepValidation.cityRequired;
    }

    return out;
}
