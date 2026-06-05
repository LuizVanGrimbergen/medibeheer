import type { AppointmentAddressGeocodeValues } from '@/lib/google-maps/geocodeAppointmentAddress';
import { geocodeAppointmentAddress } from '@/lib/google-maps/geocodeAppointmentAddress';
import type { AppointmentAddressFieldErrors } from '@/lib/patient/appointments/appointmentAddressValidation';
import appointmentsNl from '@/translations/nl/patient/appointments';

export const appointmentAddressGeocodeErrorMessages = {
    notFound: appointmentsNl.validation.addressNotFound,
    unavailable: appointmentsNl.validation.addressVerificationUnavailable,
    componentMismatch: appointmentsNl.validation.addressComponentMismatch,
} as const;

const GEOCODE_ERROR_MESSAGE_VALUES: string[] = Object.values(
    appointmentAddressGeocodeErrorMessages,
);

export function isAppointmentAddressGeocodeErrorMessage(
    message: string | undefined,
): boolean {
    return (
        message !== undefined && GEOCODE_ERROR_MESSAGE_VALUES.includes(message)
    );
}

export type AppointmentAddressGeocodeVerificationResult =
    | { valid: true }
    | { valid: false; fieldErrors: AppointmentAddressFieldErrors };

export async function verifyAppointmentAddressGeocode(
    values: AppointmentAddressGeocodeValues,
): Promise<AppointmentAddressGeocodeVerificationResult> {
    try {
        const result = await geocodeAppointmentAddress(values);

        if (result.valid) {
            return { valid: true };
        }

        if (result.reason === 'mismatch') {
            return { valid: false, fieldErrors: result.fieldErrors };
        }

        return {
            valid: false,
            fieldErrors: {
                street: appointmentAddressGeocodeErrorMessages.notFound,
            },
        };
    } catch {
        return {
            valid: false,
            fieldErrors: {
                street: appointmentAddressGeocodeErrorMessages.unavailable,
            },
        };
    }
}
