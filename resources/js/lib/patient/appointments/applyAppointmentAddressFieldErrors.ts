import type { AppointmentFormWithErrors } from '@/Components/Patient/Appointments/form/AppointmentFormTypes';
import { looseInertiaForm } from '@/lib/inertia/looseInertiaForm';
import type {
    AppointmentAddressFieldErrorKey,
    AppointmentAddressFieldErrors,
} from '@/lib/patient/appointments/appointmentAddressValidation';
import { isAppointmentAddressGeocodeErrorMessage } from '@/lib/patient/appointments/verifyAppointmentAddressGeocode';

export const APPOINTMENT_ADDRESS_FIELD_KEYS: AppointmentAddressFieldErrorKey[] =
    ['street', 'postal_code', 'city'];

export function applyAppointmentAddressFieldErrors(
    form: AppointmentFormWithErrors,
    fieldErrors: AppointmentAddressFieldErrors,
): void {
    const inertiaForm = looseInertiaForm(form);

    for (const key of APPOINTMENT_ADDRESS_FIELD_KEYS) {
        const message = fieldErrors[key];

        if (message !== undefined && message.length > 0) {
            inertiaForm.setError(key, message);
        } else {
            inertiaForm.clearErrors(key);
        }
    }
}

export function clearAppointmentAddressGeocodeFieldErrors(
    form: AppointmentFormWithErrors,
): void {
    const inertiaForm = looseInertiaForm(form);

    for (const key of APPOINTMENT_ADDRESS_FIELD_KEYS) {
        if (isAppointmentAddressGeocodeErrorMessage(form.errors[key])) {
            inertiaForm.clearErrors(key);
        }
    }
}

export function hasAppointmentAddressGeocodeFieldErrors(
    form: AppointmentFormWithErrors,
): boolean {
    return APPOINTMENT_ADDRESS_FIELD_KEYS.some((key) =>
        isAppointmentAddressGeocodeErrorMessage(form.errors[key]),
    );
}
