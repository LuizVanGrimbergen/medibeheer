import { importLibrary } from '@googlemaps/js-api-loader';
import { formatAppointmentAddress } from '@/lib/appointments/formatAppointmentAddress';
import { mapGeocoderAddressComponents } from '@/lib/google-maps/mapGeocoderAddressComponents';
import { parseGooglePlaceAddressComponents } from '@/lib/google-maps/parseGooglePlaceAddressComponents';
import type { AppointmentAddressFieldErrors } from '@/lib/patient/appointments/appointmentAddressValidation';
import type { AppointmentAddressFieldValues } from '@/lib/patient/appointments/appointmentAddressValidation';
import { collectAppointmentAddressGeocodeFieldErrors } from '@/lib/patient/appointments/matchAppointmentAddressToGeocode';
import { ensureGoogleMapsConfigured } from '@/lib/google-maps/loadGoogleMapsApi';

export type AppointmentAddressGeocodeValues = AppointmentAddressFieldValues & {
    house_number?: string;
};

export type GeocodeAppointmentAddressResult =
    | { valid: true }
    | { valid: false; reason: 'not_found' }
    | { valid: false; reason: 'mismatch'; fieldErrors: AppointmentAddressFieldErrors };

let geocodingLibraryPromise: Promise<google.maps.GeocodingLibrary> | null = null;

async function importGoogleMapsGeocodingLibrary(): Promise<google.maps.GeocodingLibrary> {
    ensureGoogleMapsConfigured();
    geocodingLibraryPromise ??= importLibrary('geocoding');

    return geocodingLibraryPromise;
}

export async function geocodeAppointmentAddress(
    values: AppointmentAddressGeocodeValues,
): Promise<GeocodeAppointmentAddressResult> {
    const address = formatAppointmentAddress({
        street: values.street,
        house_number: values.house_number ?? null,
        postal_code: values.postal_code,
        city: values.city,
    });

    if (address.trim().length < 1) {
        return { valid: false, reason: 'not_found' };
    }

    const { Geocoder } = await importGoogleMapsGeocodingLibrary();
    const geocoder = new Geocoder();

    return new Promise((resolve) => {
        geocoder.geocode(
            {
                address,
                componentRestrictions: { country: 'BE' },
            },
            (results, status) => {
                if (
                    status !== google.maps.GeocoderStatus.OK ||
                    results === null ||
                    results.length < 1
                ) {
                    resolve({ valid: false, reason: 'not_found' });

                    return;
                }

                const parsed = parseGooglePlaceAddressComponents(
                    mapGeocoderAddressComponents(results[0]!.address_components),
                );

                const fieldErrors = collectAppointmentAddressGeocodeFieldErrors(
                    values,
                    parsed,
                );

                if (Object.keys(fieldErrors).length > 0) {
                    resolve({
                        valid: false,
                        reason: 'mismatch',
                        fieldErrors,
                    });

                    return;
                }

                resolve({ valid: true });
            },
        );
    });
}
