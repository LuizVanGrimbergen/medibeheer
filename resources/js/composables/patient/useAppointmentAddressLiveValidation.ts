import { watchDebounced } from '@vueuse/core';
import type { Ref } from 'vue';
import { ref } from 'vue';
import type { AppointmentFormWithErrors } from '@/Components/Patient/Appointments/form/AppointmentFormTypes';
import { formatAppointmentAddress } from '@/lib/appointments/formatAppointmentAddress';
import { getGoogleMapsApiKey } from '@/lib/google-maps/loadGoogleMapsApi';
import {
    applyAppointmentAddressFieldErrors,
    clearAppointmentAddressGeocodeFieldErrors,
} from '@/lib/patient/appointments/applyAppointmentAddressFieldErrors';
import { collectAppointmentAddressFieldErrors } from '@/lib/patient/appointments/appointmentAddressValidation';
import { isAppointmentAddressComplete } from '@/lib/patient/appointments/isAppointmentAddressComplete';
import { verifyAppointmentAddressGeocode } from '@/lib/patient/appointments/verifyAppointmentAddressGeocode';

export function useAppointmentAddressLiveValidation(options: {
    form: AppointmentFormWithErrors;
    addressRequired: Ref<boolean>;
    enabled: Ref<boolean>;
    placesVerifiedSnapshot: Ref<string | null>;
}): { isVerifyingGeocode: Ref<boolean> } {
    const isVerifyingGeocode = ref(false);
    let geocodeRequestId = 0;

    watchDebounced(
        () => {
            if (!options.enabled.value) {
                return null;
            }

            return [
                options.form.street,
                options.form.house_number,
                options.form.postal_code,
                options.form.city,
            ] as const;
        },
        () => {
            void (async () => {
                if (!options.enabled.value) {
                    return;
                }

                const syncErrors = collectAppointmentAddressFieldErrors(
                    options.form,
                    { required: options.addressRequired.value },
                );

                applyAppointmentAddressFieldErrors(options.form, syncErrors);

                if (Object.keys(syncErrors).length > 0) {
                    clearAppointmentAddressGeocodeFieldErrors(options.form);

                    return;
                }

                if (!isAppointmentAddressComplete(options.form)) {
                    clearAppointmentAddressGeocodeFieldErrors(options.form);

                    return;
                }

                if (getGoogleMapsApiKey() === null) {
                    return;
                }

                const currentSnapshot = formatAppointmentAddress(options.form);

                if (
                    options.placesVerifiedSnapshot.value !== null &&
                    currentSnapshot === options.placesVerifiedSnapshot.value
                ) {
                    clearAppointmentAddressGeocodeFieldErrors(options.form);

                    return;
                }

                const requestId = ++geocodeRequestId;
                isVerifyingGeocode.value = true;

                try {
                    const result = await verifyAppointmentAddressGeocode({
                        street: options.form.street,
                        house_number: options.form.house_number,
                        postal_code: options.form.postal_code,
                        city: options.form.city,
                    });

                    if (requestId !== geocodeRequestId) {
                        return;
                    }

                    if (!result.valid) {
                        applyAppointmentAddressFieldErrors(
                            options.form,
                            result.fieldErrors,
                        );
                    } else {
                        clearAppointmentAddressGeocodeFieldErrors(options.form);
                    }
                } finally {
                    if (requestId === geocodeRequestId) {
                        isVerifyingGeocode.value = false;
                    }
                }
            })();
        },
        { debounce: 600 },
    );

    return { isVerifyingGeocode };
}
